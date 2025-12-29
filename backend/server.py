from fastapi import FastAPI, APIRouter, HTTPException, UploadFile, File, Form, Depends
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from fastapi.responses import FileResponse
from dotenv import load_dotenv
from starlette.middleware.cors import CORSMiddleware
from motor.motor_asyncio import AsyncIOMotorClient
from googleapiclient.discovery import build
from googleapiclient.errors import HttpError
import os
import logging
from pathlib import Path
from pydantic import BaseModel, Field, ConfigDict
from typing import List, Optional
import uuid
from datetime import datetime, timezone, timedelta
import jwt
import bcrypt
import aiofiles

ROOT_DIR = Path(__file__).parent
load_dotenv(ROOT_DIR / '.env')

# MongoDB connection
mongo_url = os.environ['MONGO_URL']
client = AsyncIOMotorClient(mongo_url)
db = client[os.environ['DB_NAME']]

# JWT Secret
JWT_SECRET = os.environ.get('JWT_SECRET', 'your-secret-key-change-in-production')
JWT_ALGORITHM = 'HS256'

# Create the main app
app = FastAPI()
api_router = APIRouter(prefix="/api")
security = HTTPBearer()

# File storage path
UPLOAD_DIR = ROOT_DIR / 'uploads'
UPLOAD_DIR.mkdir(exist_ok=True)

# Models
class AdminLogin(BaseModel):
    username: str
    password: str

class AdminCreate(BaseModel):
    username: str
    password: str

class Song(BaseModel):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    title: str
    artist: str
    source_type: str  # 'youtube', 'upload', 'url', 'midi', 'kar'
    source_url: Optional[str] = None
    file_path: Optional[str] = None
    thumbnail: Optional[str] = None
    duration: Optional[int] = None
    lyrics: Optional[str] = None
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class SongCreate(BaseModel):
    title: str
    artist: str
    source_type: str
    source_url: Optional[str] = None
    thumbnail: Optional[str] = None
    duration: Optional[int] = None
    lyrics: Optional[str] = None

class Score(BaseModel):
    model_config = ConfigDict(extra="ignore")
    id: str = Field(default_factory=lambda: str(uuid.uuid4()))
    song_id: str
    player_name: str
    score: int
    accuracy: float
    created_at: datetime = Field(default_factory=lambda: datetime.now(timezone.utc))

class ScoreCreate(BaseModel):
    song_id: str
    player_name: str
    score: int
    accuracy: float

# Helper functions
def create_access_token(data: dict):
    to_encode = data.copy()
    expire = datetime.now(timezone.utc) + timedelta(hours=24)
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, JWT_SECRET, algorithm=JWT_ALGORITHM)
    return encoded_jwt

def verify_token(credentials: HTTPAuthorizationCredentials = Depends(security)):
    try:
        payload = jwt.decode(credentials.credentials, JWT_SECRET, algorithms=[JWT_ALGORITHM])
        return payload
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=401, detail="Token expired")
    except jwt.InvalidTokenError:
        raise HTTPException(status_code=401, detail="Invalid token")

# Initialize default admin if not exists
async def init_admin():
    admin = await db.admins.find_one({"username": "admin"})
    if not admin:
        hashed = bcrypt.hashpw("admin123".encode('utf-8'), bcrypt.gensalt())
        await db.admins.insert_one({
            "username": "admin",
            "password": hashed.decode('utf-8')
        })
        logging.info("Default admin created: username=admin, password=admin123")

# Auth endpoints
@api_router.post("/auth/login")
async def login(admin: AdminLogin):
    db_admin = await db.admins.find_one({"username": admin.username})
    if not db_admin:
        raise HTTPException(status_code=401, detail="Invalid credentials")
    
    if not bcrypt.checkpw(admin.password.encode('utf-8'), db_admin['password'].encode('utf-8')):
        raise HTTPException(status_code=401, detail="Invalid credentials")
    
    token = create_access_token({"username": admin.username})
    return {"token": token, "username": admin.username}

# YouTube search endpoint
@api_router.get("/youtube/search")
async def youtube_search(q: str, max_results: int = 10, api_key: Optional[str] = None):
    if not api_key:
        raise HTTPException(status_code=400, detail="YouTube API key required")
    
    try:
        youtube = build('youtube', 'v3', developerKey=api_key, cache_discovery=False)
        
        request = youtube.search().list(
            part="snippet",
            q=q + " karaoke",
            type="video",
            maxResults=max_results,
            videoCategoryId="10"  # Music category
        )
        response = request.execute()
        
        results = []
        for item in response.get('items', []):
            results.append({
                "video_id": item['id']['videoId'],
                "title": item['snippet']['title'],
                "thumbnail": item['snippet']['thumbnails']['medium']['url'],
                "channel": item['snippet']['channelTitle']
            })
        
        return {"results": results}
    except HttpError as e:
        if "quotaExceeded" in str(e):
            raise HTTPException(status_code=429, detail="YouTube API quota exceeded")
        raise HTTPException(status_code=400, detail=str(e))

# Song CRUD endpoints
@api_router.post("/songs", response_model=Song)
async def create_song(song: SongCreate, _: dict = Depends(verify_token)):
    song_obj = Song(**song.model_dump())
    doc = song_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    await db.songs.insert_one(doc)
    return song_obj

@api_router.post("/songs/upload")
async def upload_song(
    title: str = Form(...),
    artist: str = Form(...),
    file: UploadFile = File(...),
    lyrics: Optional[str] = Form(None),
    _: dict = Depends(verify_token)
):
    # Save file
    file_ext = Path(file.filename).suffix
    file_id = str(uuid.uuid4())
    file_path = UPLOAD_DIR / f"{file_id}{file_ext}"
    
    async with aiofiles.open(file_path, 'wb') as f:
        content = await file.read()
        await f.write(content)
    
    # Determine source type
    source_type = 'midi' if file_ext in ['.mid', '.midi'] else 'kar' if file_ext == '.kar' else 'upload'
    
    # Create song
    song = Song(
        title=title,
        artist=artist,
        source_type=source_type,
        file_path=str(file_path),
        lyrics=lyrics
    )
    
    doc = song.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    await db.songs.insert_one(doc)
    
    return song

@api_router.get("/songs", response_model=List[Song])
async def get_songs(search: Optional[str] = None):
    query = {}
    if search:
        query = {"$or": [
            {"title": {"$regex": search, "$options": "i"}},
            {"artist": {"$regex": search, "$options": "i"}}
        ]}
    
    songs = await db.songs.find(query, {"_id": 0}).sort("created_at", -1).to_list(100)
    
    for song in songs:
        if isinstance(song.get('created_at'), str):
            song['created_at'] = datetime.fromisoformat(song['created_at'])
    
    return songs

@api_router.get("/songs/{song_id}", response_model=Song)
async def get_song(song_id: str):
    song = await db.songs.find_one({"id": song_id}, {"_id": 0})
    if not song:
        raise HTTPException(status_code=404, detail="Song not found")
    
    if isinstance(song.get('created_at'), str):
        song['created_at'] = datetime.fromisoformat(song['created_at'])
    
    return song

@api_router.delete("/songs/{song_id}")
async def delete_song(song_id: str, _: dict = Depends(verify_token)):
    song = await db.songs.find_one({"id": song_id})
    if not song:
        raise HTTPException(status_code=404, detail="Song not found")
    
    # Delete file if exists
    if song.get('file_path'):
        file_path = Path(song['file_path'])
        if file_path.exists():
            file_path.unlink()
    
    await db.songs.delete_one({"id": song_id})
    return {"message": "Song deleted"}

# Score endpoints
@api_router.post("/scores", response_model=Score)
async def create_score(score: ScoreCreate):
    score_obj = Score(**score.model_dump())
    doc = score_obj.model_dump()
    doc['created_at'] = doc['created_at'].isoformat()
    await db.scores.insert_one(doc)
    return score_obj

@api_router.get("/scores/{song_id}")
async def get_song_scores(song_id: str, limit: int = 10):
    scores = await db.scores.find(
        {"song_id": song_id},
        {"_id": 0}
    ).sort("score", -1).limit(limit).to_list(limit)
    
    for score in scores:
        if isinstance(score.get('created_at'), str):
            score['created_at'] = datetime.fromisoformat(score['created_at'])
    
    return scores

@api_router.get("/leaderboard")
async def get_leaderboard(limit: int = 20):
    scores = await db.scores.find(
        {},
        {"_id": 0}
    ).sort("score", -1).limit(limit).to_list(limit)
    
    for score in scores:
        if isinstance(score.get('created_at'), str):
            score['created_at'] = datetime.fromisoformat(score['created_at'])
    
    return scores

# File serving
@api_router.get("/files/{file_id}")
async def serve_file(file_id: str):
    # Find file in uploads directory
    files = list(UPLOAD_DIR.glob(f"{file_id}*"))
    if not files:
        raise HTTPException(status_code=404, detail="File not found")
    
    return FileResponse(files[0])

# Include router
app.include_router(api_router)

app.add_middleware(
    CORSMiddleware,
    allow_credentials=True,
    allow_origins=os.environ.get('CORS_ORIGINS', '*').split(','),
    allow_methods=["*"],
    allow_headers=["*"],
)

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@app.on_event("startup")
async def startup():
    await init_admin()

@app.on_event("shutdown")
async def shutdown_db_client():
    client.close()
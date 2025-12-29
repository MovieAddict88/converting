import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Upload, Search, Trash2, Plus, Youtube, LogOut, Music } from 'lucide-react';
import axios from 'axios';
import { motion } from 'framer-motion';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

const AdminDashboard = () => {
  const [songs, setSongs] = useState([]);
  const [youtubeQuery, setYoutubeQuery] = useState('');
  const [youtubeResults, setYoutubeResults] = useState([]);
  const [youtubeApiKey, setYoutubeApiKey] = useState('');
  const [showYoutubeSearch, setShowYoutubeSearch] = useState(false);
  const [showUploadForm, setShowUploadForm] = useState(false);
  const [uploadData, setUploadData] = useState({
    title: '',
    artist: '',
    file: null,
    lyrics: ''
  });
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const token = localStorage.getItem('admin_token');
    if (!token) {
      navigate('/admin/login');
      return;
    }
    fetchSongs();
  }, []);

  const fetchSongs = async () => {
    try {
      const response = await axios.get(`${API}/songs`);
      setSongs(response.data);
    } catch (error) {
      console.error('Error fetching songs:', error);
    }
  };

  const searchYoutube = async () => {
    if (!youtubeApiKey) {
      alert('Please enter YouTube API key');
      return;
    }
    
    setLoading(true);
    try {
      const response = await axios.get(`${API}/youtube/search`, {
        params: {
          q: youtubeQuery,
          api_key: youtubeApiKey,
          max_results: 10
        }
      });
      setYoutubeResults(response.data.results);
    } catch (error) {
      alert(error.response?.data?.detail || 'YouTube search failed');
    } finally {
      setLoading(false);
    }
  };

  const addYoutubeSong = async (video) => {
    const token = localStorage.getItem('admin_token');
    try {
      await axios.post(
        `${API}/songs`,
        {
          title: video.title,
          artist: video.channel,
          source_type: 'youtube',
          source_url: `https://www.youtube.com/watch?v=${video.video_id}`,
          thumbnail: video.thumbnail
        },
        {
          headers: { Authorization: `Bearer ${token}` }
        }
      );
      alert('Song added successfully!');
      fetchSongs();
      setShowYoutubeSearch(false);
      setYoutubeResults([]);
    } catch (error) {
      alert(error.response?.data?.detail || 'Failed to add song');
    }
  };

  const handleFileUpload = async (e) => {
    e.preventDefault();
    const token = localStorage.getItem('admin_token');
    
    if (!uploadData.file) {
      alert('Please select a file');
      return;
    }

    const formData = new FormData();
    formData.append('title', uploadData.title);
    formData.append('artist', uploadData.artist);
    formData.append('file', uploadData.file);
    if (uploadData.lyrics) {
      formData.append('lyrics', uploadData.lyrics);
    }

    setLoading(true);
    try {
      await axios.post(`${API}/songs/upload`, formData, {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      });
      alert('Song uploaded successfully!');
      fetchSongs();
      setShowUploadForm(false);
      setUploadData({ title: '', artist: '', file: null, lyrics: '' });
    } catch (error) {
      alert(error.response?.data?.detail || 'Upload failed');
    } finally {
      setLoading(false);
    }
  };

  const deleteSong = async (songId) => {
    if (!window.confirm('Are you sure you want to delete this song?')) return;
    
    const token = localStorage.getItem('admin_token');
    try {
      await axios.delete(`${API}/songs/${songId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      alert('Song deleted');
      fetchSongs();
    } catch (error) {
      alert(error.response?.data?.detail || 'Delete failed');
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('admin_token');
    localStorage.removeItem('admin_username');
    navigate('/admin/login');
  };

  return (
    <div className="min-h-screen bg-background" data-testid="admin-dashboard">
      {/* Header */}
      <header className="border-b border-border bg-card p-6">
        <div className="max-w-7xl mx-auto flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-unbounded font-bold" data-testid="dashboard-title">Admin Dashboard</h1>
            <p className="text-muted-foreground mt-1">Manage your karaoke library</p>
          </div>
          <button
            onClick={handleLogout}
            className="flex items-center gap-2 px-4 py-2 text-muted-foreground hover:text-error transition-colors"
            data-testid="logout-button"
          >
            <LogOut className="w-5 h-5" />
            Logout
          </button>
        </div>
      </header>

      <div className="max-w-7xl mx-auto p-6">
        {/* Action Buttons */}
        <div className="flex flex-wrap gap-4 mb-8">
          <button
            onClick={() => setShowUploadForm(!showUploadForm)}
            className="flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-full font-bold shadow-neon-pink hover:scale-105 transition-all"
            data-testid="upload-button"
          >
            <Upload className="w-5 h-5" />
            Upload File
          </button>
          <button
            onClick={() => setShowYoutubeSearch(!showYoutubeSearch)}
            className="flex items-center gap-2 px-6 py-3 bg-secondary text-black rounded-full font-bold shadow-neon-blue hover:scale-105 transition-all"
            data-testid="youtube-search-button"
          >
            <Youtube className="w-5 h-5" />
            YouTube Search
          </button>
        </div>

        {/* Upload Form */}
        {showUploadForm && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="backdrop-blur-xl rounded-2xl border border-white/10 p-6 mb-8"
            style={{ background: 'rgba(0, 0, 0, 0.4)' }}
            data-testid="upload-form"
          >
            <h3 className="text-xl font-bold mb-4">Upload Song File</h3>
            <form onSubmit={handleFileUpload} className="space-y-4">
              <div>
                <label className="block text-sm mb-2">Title</label>
                <input
                  type="text"
                  value={uploadData.title}
                  onChange={(e) => setUploadData({...uploadData, title: e.target.value})}
                  className="w-full bg-input/50 border border-border rounded-lg px-4 py-2 text-white"
                  required
                  data-testid="upload-title-input"
                />
              </div>
              <div>
                <label className="block text-sm mb-2">Artist</label>
                <input
                  type="text"
                  value={uploadData.artist}
                  onChange={(e) => setUploadData({...uploadData, artist: e.target.value})}
                  className="w-full bg-input/50 border border-border rounded-lg px-4 py-2 text-white"
                  required
                  data-testid="upload-artist-input"
                />
              </div>
              <div>
                <label className="block text-sm mb-2">File (.mp4, .mid, .kar, .mp3)</label>
                <input
                  type="file"
                  accept=".mp4,.mid,.midi,.kar,.mp3,.webm"
                  onChange={(e) => setUploadData({...uploadData, file: e.target.files[0]})}
                  className="w-full bg-input/50 border border-border rounded-lg px-4 py-2 text-white"
                  required
                  data-testid="upload-file-input"
                />
              </div>
              <div>
                <label className="block text-sm mb-2">Lyrics (optional)</label>
                <textarea
                  value={uploadData.lyrics}
                  onChange={(e) => setUploadData({...uploadData, lyrics: e.target.value})}
                  className="w-full bg-input/50 border border-border rounded-lg px-4 py-2 text-white h-24"
                  placeholder="Add lyrics..."
                  data-testid="upload-lyrics-input"
                />
              </div>
              <button
                type="submit"
                disabled={loading}
                className="px-6 py-2 bg-primary text-white rounded-full font-bold hover:scale-105 transition-all disabled:opacity-50"
                data-testid="submit-upload-button"
              >
                {loading ? 'Uploading...' : 'Upload Song'}
              </button>
            </form>
          </motion.div>
        )}

        {/* YouTube Search */}
        {showYoutubeSearch && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="backdrop-blur-xl rounded-2xl border border-white/10 p-6 mb-8"
            style={{ background: 'rgba(0, 0, 0, 0.4)' }}
            data-testid="youtube-search-section"
          >
            <h3 className="text-xl font-bold mb-4">Search YouTube</h3>
            <div className="space-y-4">
              <div>
                <label className="block text-sm mb-2">YouTube API Key</label>
                <input
                  type="password"
                  value={youtubeApiKey}
                  onChange={(e) => setYoutubeApiKey(e.target.value)}
                  className="w-full bg-input/50 border border-border rounded-lg px-4 py-2 text-white"
                  placeholder="Enter your YouTube Data API v3 key"
                  data-testid="youtube-api-key-input"
                />
              </div>
              <div className="flex gap-2">
                <input
                  type="text"
                  value={youtubeQuery}
                  onChange={(e) => setYoutubeQuery(e.target.value)}
                  onKeyPress={(e) => e.key === 'Enter' && searchYoutube()}
                  className="flex-1 bg-input/50 border border-border rounded-lg px-4 py-2 text-white"
                  placeholder="Search for karaoke songs..."
                  data-testid="youtube-search-input"
                />
                <button
                  onClick={searchYoutube}
                  disabled={loading}
                  className="px-6 py-2 bg-secondary text-black rounded-full font-bold hover:scale-105 transition-all disabled:opacity-50"
                  data-testid="youtube-search-submit"
                >
                  {loading ? 'Searching...' : 'Search'}
                </button>
              </div>
            </div>

            {youtubeResults.length > 0 && (
              <div className="mt-6 space-y-3" data-testid="youtube-results">
                {youtubeResults.map((video) => (
                  <div key={video.video_id} className="flex items-center gap-4 p-4 bg-muted rounded-lg" data-testid={`youtube-result-${video.video_id}`}>
                    <img src={video.thumbnail} alt={video.title} className="w-32 h-20 object-cover rounded" />
                    <div className="flex-1">
                      <p className="font-bold line-clamp-1">{video.title}</p>
                      <p className="text-sm text-muted-foreground">{video.channel}</p>
                    </div>
                    <button
                      onClick={() => addYoutubeSong(video)}
                      className="px-4 py-2 bg-primary text-white rounded-full font-bold hover:scale-105 transition-all"
                      data-testid={`add-youtube-${video.video_id}`}
                    >
                      <Plus className="w-5 h-5" />
                    </button>
                  </div>
                ))}
              </div>
            )}
          </motion.div>
        )}

        {/* Songs Table */}
        <div className="backdrop-blur-xl rounded-2xl border border-white/10 overflow-hidden" style={{ background: 'rgba(0, 0, 0, 0.4)' }}>
          <div className="p-6 border-b border-border">
            <h3 className="text-xl font-bold" data-testid="songs-list-title">Songs Library ({songs.length})</h3>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-border">
                  <th className="text-left p-4 text-sm font-bold text-muted-foreground uppercase tracking-wider">Title</th>
                  <th className="text-left p-4 text-sm font-bold text-muted-foreground uppercase tracking-wider">Artist</th>
                  <th className="text-left p-4 text-sm font-bold text-muted-foreground uppercase tracking-wider">Type</th>
                  <th className="text-left p-4 text-sm font-bold text-muted-foreground uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody>
                {songs.map((song) => (
                  <tr key={song.id} className="border-b border-border/50 hover:bg-white/5 transition-colors" data-testid={`song-row-${song.id}`}>
                    <td className="p-4">
                      <div className="flex items-center gap-3">
                        {song.thumbnail ? (
                          <img src={song.thumbnail} alt={song.title} className="w-12 h-12 object-cover rounded" />
                        ) : (
                          <div className="w-12 h-12 bg-muted rounded flex items-center justify-center">
                            <Music className="w-6 h-6 text-muted-foreground" />
                          </div>
                        )}
                        <span className="font-medium">{song.title}</span>
                      </div>
                    </td>
                    <td className="p-4 text-muted-foreground">{song.artist}</td>
                    <td className="p-4">
                      <span className="px-3 py-1 bg-primary/10 border border-primary/30 rounded-full text-xs font-mono uppercase tracking-wider text-primary">
                        {song.source_type}
                      </span>
                    </td>
                    <td className="p-4">
                      <button
                        onClick={() => deleteSong(song.id)}
                        className="p-2 text-error hover:bg-error/10 rounded transition-colors"
                        data-testid={`delete-song-${song.id}`}
                      >
                        <Trash2 className="w-5 h-5" />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            {songs.length === 0 && (
              <div className="p-12 text-center text-muted-foreground" data-testid="empty-songs-list">
                No songs yet. Add your first song!
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdminDashboard;
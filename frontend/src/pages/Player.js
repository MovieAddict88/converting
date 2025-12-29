import { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Mic, Volume2, Award } from 'lucide-react';
import axios from 'axios';
import { motion, AnimatePresence } from 'framer-motion';
import PitchDetector from '../utils/PitchDetector';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

const Player = () => {
  const { songId } = useParams();
  const navigate = useNavigate();
  const [song, setSong] = useState(null);
  const [isPlaying, setIsPlaying] = useState(false);
  const [playerName, setPlayerName] = useState('');
  const [showNamePrompt, setShowNamePrompt] = useState(true);
  const [score, setScore] = useState(0);
  const [accuracy, setAccuracy] = useState(0);
  const [feedback, setFeedback] = useState(null);
  const [isRecording, setIsRecording] = useState(false);
  const pitchDetectorRef = useRef(null);
  const videoRef = useRef(null);

  useEffect(() => {
    fetchSong();
  }, [songId]);

  const fetchSong = async () => {
    try {
      const response = await axios.get(`${API}/songs/${songId}`);
      setSong(response.data);
    } catch (error) {
      console.error('Error fetching song:', error);
    }
  };

  const startPerformance = async () => {
    if (!playerName) {
      alert('Please enter your name');
      return;
    }
    setShowNamePrompt(false);
    setIsPlaying(true);
    
    // Initialize pitch detector
    pitchDetectorRef.current = new PitchDetector((pitch) => {
      // Simulate scoring based on pitch detection
      if (pitch > 0) {
        const points = Math.floor(Math.random() * 10) + 5; // Simplified scoring
        setScore(prev => prev + points);
        setAccuracy(prev => Math.min((prev * 0.9 + Math.random() * 100 * 0.1), 100));
        
        // Show feedback
        const feedbacks = ['Perfect!', 'Great!', 'Good!', 'Nice!'];
        setFeedback(feedbacks[Math.floor(Math.random() * feedbacks.length)]);
        setTimeout(() => setFeedback(null), 800);
      }
    });
    
    try {
      await pitchDetectorRef.current.start();
      setIsRecording(true);
    } catch (error) {
      console.error('Error starting pitch detection:', error);
      alert('Microphone access required for karaoke!');
    }
  };

  const stopPerformance = async () => {
    setIsPlaying(false);
    setIsRecording(false);
    
    if (pitchDetectorRef.current) {
      pitchDetectorRef.current.stop();
    }
    
    // Save score
    try {
      await axios.post(`${API}/scores`, {
        song_id: songId,
        player_name: playerName,
        score: score,
        accuracy: accuracy
      });
      
      alert(`Great performance ${playerName}! Score: ${score}, Accuracy: ${accuracy.toFixed(1)}%`);
      navigate('/');
    } catch (error) {
      console.error('Error saving score:', error);
    }
  };

  const getVideoEmbedUrl = (url) => {
    if (!url) return null;
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
      const videoId = url.match(/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
      return videoId ? `https://www.youtube.com/embed/${videoId[1]}?autoplay=${isPlaying ? 1 : 0}&controls=1` : null;
    }
    return url;
  };

  if (!song) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center" data-testid="loading-container">
        <div className="text-center">
          <div className="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p className="text-muted-foreground">Loading song...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background relative overflow-hidden" data-testid="player-container">
      {/* Background */}
      <div className="absolute inset-0 z-0">
        {song.source_type === 'youtube' && song.source_url ? (
          <iframe
            ref={videoRef}
            src={getVideoEmbedUrl(song.source_url)}
            className="w-full h-full"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowFullScreen
            data-testid="youtube-player"
          />
        ) : (
          <div 
            className="w-full h-full"
            style={{
              backgroundImage: `url('https://images.unsplash.com/photo-1759771963975-8a4885446f1f?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTY2Njl8MHwxfHNlYXJjaHwzfHxhYnN0cmFjdCUyMG5lb24lMjBzb3VuZCUyMHdhdmVzfGVufDB8fHx8MTc2NzAzNjc1Mnww&ixlib=rb-4.1.0&q=85')`,
              backgroundSize: 'cover',
              backgroundPosition: 'center'
            }}
          >
            <div className="absolute inset-0 bg-black/50"></div>
          </div>
        )}
      </div>

      {/* Top Controls */}
      <div className="absolute top-0 left-0 right-0 z-50 p-6 bg-gradient-to-b from-black/80 to-transparent">
        <button
          onClick={() => navigate('/')}
          className="flex items-center gap-2 text-white hover:text-primary transition-colors"
          data-testid="back-button"
        >
          <ArrowLeft className="w-5 h-5" />
          <span>Back</span>
        </button>
      </div>

      {/* Score Display */}
      <div className="absolute top-20 right-6 z-50 backdrop-blur-xl rounded-2xl border border-white/10 p-6" style={{ background: 'rgba(0, 0, 0, 0.6)' }} data-testid="score-display">
        <div className="text-center mb-4">
          <Award className="w-8 h-8 text-primary mx-auto mb-2" />
          <p className="text-sm text-muted-foreground">Score</p>
          <p className="text-4xl font-unbounded font-black text-primary" data-testid="current-score">{score}</p>
        </div>
        <div className="text-center">
          <p className="text-sm text-muted-foreground">Accuracy</p>
          <p className="text-2xl font-bold" data-testid="current-accuracy">{accuracy.toFixed(1)}%</p>
        </div>
        {isRecording && (
          <div className="mt-4 flex items-center justify-center gap-2 text-error" data-testid="recording-indicator">
            <div className="w-3 h-3 bg-error rounded-full animate-pulse"></div>
            <span className="text-xs">Recording</span>
          </div>
        )}
      </div>

      {/* Feedback Popup */}
      <AnimatePresence>
        {feedback && (
          <motion.div
            initial={{ opacity: 0, scale: 0.5, y: 50 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.5, y: -50 }}
            className="absolute top-1/3 left-1/2 -translate-x-1/2 z-50"
            data-testid="feedback-popup"
          >
            <div className="text-6xl font-unbounded font-black text-success" style={{ textShadow: '0 0 30px rgba(0, 255, 148, 0.8)' }}>
              {feedback}
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Bottom Controls & Lyrics */}
      <div className="absolute bottom-0 left-0 right-0 z-50 p-6 bg-gradient-to-t from-black/90 to-transparent">
        {song.lyrics && isPlaying && (
          <div className="text-center mb-8" data-testid="lyrics-display">
            <p className="text-4xl font-syne font-bold mb-2" style={{ textShadow: '0 0 20px rgba(255, 255, 255, 0.5)' }}>
              {song.lyrics.split('\n')[0]}
            </p>
            <p className="text-2xl text-gray-400">
              {song.lyrics.split('\n')[1] || ''}
            </p>
          </div>
        )}

        <div className="max-w-2xl mx-auto text-center">
          <h2 className="text-3xl font-unbounded font-bold mb-2" data-testid="player-song-title">{song.title}</h2>
          <p className="text-xl text-muted-foreground mb-6" data-testid="player-song-artist">{song.artist}</p>

          {!isPlaying ? (
            <button
              onClick={startPerformance}
              disabled={showNamePrompt && !playerName}
              className="px-12 py-4 bg-primary text-white rounded-full font-bold tracking-wide uppercase text-lg shadow-neon-pink hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
              data-testid="start-button"
            >
              Start Singing
            </button>
          ) : (
            <button
              onClick={stopPerformance}
              className="px-12 py-4 bg-error text-white rounded-full font-bold tracking-wide uppercase text-lg hover:scale-105 transition-all duration-300"
              data-testid="stop-button"
            >
              Stop & Save Score
            </button>
          )}
        </div>
      </div>

      {/* Name Prompt Modal */}
      {showNamePrompt && (
        <div className="absolute inset-0 z-[100] flex items-center justify-center backdrop-blur-sm" style={{ background: 'rgba(0, 0, 0, 0.8)' }} data-testid="name-prompt-modal">
          <motion.div 
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            className="backdrop-blur-xl rounded-2xl border border-white/10 p-8 max-w-md w-full mx-4"
            style={{ background: 'rgba(0, 0, 0, 0.6)' }}
          >
            <h3 className="text-2xl font-unbounded font-bold mb-4 text-center">Ready to Shine?</h3>
            <p className="text-muted-foreground mb-6 text-center">Enter your name to start your performance</p>
            <input
              type="text"
              placeholder="Your name"
              value={playerName}
              onChange={(e) => setPlayerName(e.target.value)}
              className="w-full bg-input/50 border border-border focus:border-primary focus:ring-2 focus:ring-primary/50 rounded-lg text-white placeholder:text-muted-foreground/50 h-12 px-4 mb-6 transition-all"
              data-testid="player-name-input"
              onKeyPress={(e) => e.key === 'Enter' && startPerformance()}
            />
            <button
              onClick={startPerformance}
              className="w-full px-8 py-3 bg-primary text-white rounded-full font-bold tracking-wide uppercase shadow-neon-pink hover:scale-105 transition-all duration-300"
              data-testid="confirm-name-button"
            >
              Let's Go!
            </button>
          </motion.div>
        </div>
      )}
    </div>
  );
};

export default Player;
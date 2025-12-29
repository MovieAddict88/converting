import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Search, Mic2, TrendingUp } from 'lucide-react';
import axios from 'axios';
import { motion } from 'framer-motion';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

const Home = () => {
  const [songs, setSongs] = useState([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    fetchSongs();
  }, []);

  const fetchSongs = async (searchQuery = '') => {
    try {
      setLoading(true);
      const response = await axios.get(`${API}/songs`, {
        params: searchQuery ? { search: searchQuery } : {}
      });
      setSongs(response.data);
    } catch (error) {
      console.error('Error fetching songs:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    fetchSongs(search);
  };

  const playSong = (songId) => {
    navigate(`/player/${songId}`);
  };

  return (
    <div className="min-h-screen bg-background">
      {/* Glass Header */}
      <header 
        className="fixed top-0 w-full z-50 backdrop-blur-lg border-b border-white/5 h-20 flex items-center px-6 md:px-12 justify-between"
        style={{ background: 'rgba(0, 0, 0, 0.2)' }}
        data-testid="home-header"
      >
        <div className="flex items-center gap-3">
          <Mic2 className="w-8 h-8 text-primary" />
          <h1 className="text-2xl font-unbounded font-bold tracking-tight" data-testid="app-title">
            NeonVox
          </h1>
        </div>
        <button
          onClick={() => navigate('/admin/login')}
          className="text-sm text-muted-foreground hover:text-white transition-colors px-4 py-2"
          data-testid="admin-login-link"
        >
          Admin
        </button>
      </header>

      {/* Hero Section */}
      <section 
        className="relative pt-32 pb-20 px-6 md:px-12 overflow-hidden"
        data-testid="hero-section"
      >
        {/* Background Image with Overlay */}
        <div 
          className="absolute inset-0 z-0"
          style={{
            backgroundImage: `url('https://images.unsplash.com/photo-1683582544815-66d1ea55d3c2?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTY2NzZ8MHwxfHNlYXJjaHwxfHxwYXJ0eSUyMGNyb3dkJTIwc2lsaG91ZXR0ZSUyMGNvbmNlcnR8ZW58MHx8fHwxNzY3MDM2NzUwfDA&ixlib=rb-4.1.0&q=85')`,
            backgroundSize: 'cover',
            backgroundPosition: 'center'
          }}
        >
          <div className="absolute inset-0 bg-gradient-to-b from-background via-background/80 to-background"></div>
        </div>

        <div className="relative z-10 max-w-4xl mx-auto text-center">
          <motion.h2 
            className="text-6xl md:text-8xl font-unbounded font-black tracking-tighter mb-6"
            style={{ 
              background: 'linear-gradient(135deg, #FF0099 0%, #7000FF 100%)',
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent',
              backgroundClip: 'text'
            }}
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            data-testid="hero-title"
          >
            Enter the Stage
          </motion.h2>
          <motion.p 
            className="text-xl text-muted-foreground mb-12"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.3, duration: 0.8 }}
            data-testid="hero-subtitle"
          >
            Sing your heart out with real-time scoring
          </motion.p>

          {/* Search Bar */}
          <motion.form 
            onSubmit={handleSearch}
            className="max-w-2xl mx-auto"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.5, duration: 0.8 }}
          >
            <div 
              className="relative backdrop-blur-xl rounded-full border border-white/10 overflow-hidden"
              style={{ background: 'rgba(0, 0, 0, 0.4)' }}
            >
              <Search className="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
              <input
                type="text"
                placeholder="Search songs or artists..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="w-full bg-transparent border-0 pl-16 pr-6 py-5 text-white placeholder:text-muted-foreground/50 focus:outline-none focus:ring-2 focus:ring-primary/50 rounded-full"
                data-testid="search-input"
              />
            </div>
          </motion.form>
        </div>
      </section>

      {/* Songs Grid */}
      <section className="px-6 md:px-12 pb-20" data-testid="songs-section">
        <div className="max-w-7xl mx-auto">
          <div className="flex items-center gap-3 mb-8">
            <TrendingUp className="w-6 h-6 text-primary" />
            <h3 className="text-3xl font-unbounded font-bold tracking-tight" data-testid="trending-title">
              Trending Now
            </h3>
          </div>

          {loading ? (
            <div className="text-center py-20 text-muted-foreground" data-testid="loading-state">
              Loading songs...
            </div>
          ) : songs.length === 0 ? (
            <div className="text-center py-20" data-testid="empty-state">
              <img 
                src="https://images.unsplash.com/photo-1719437364093-82c24d719303?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NDk1Nzd8MHwxfHNlYXJjaHwxfHxuZW9uJTIwa2FyYW9rZSUyMG1pY3JvcGhvbmUlMjBzdGFnZXxlbnwwfHx8fDE3NjcwMzY3NDh8MA&ixlib=rb-4.1.0&q=85"
                alt="No songs"
                className="w-64 h-64 mx-auto mb-6 rounded-2xl object-cover opacity-50"
              />
              <p className="text-xl text-muted-foreground">No songs found. Ask admin to add some!</p>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {songs.map((song, index) => (
                <motion.div
                  key={song.id}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: index * 0.1, duration: 0.5 }}
                  className="group relative backdrop-blur-xl rounded-2xl border border-white/10 overflow-hidden hover:border-white/20 transition-all duration-300 cursor-pointer"
                  style={{ background: 'rgba(0, 0, 0, 0.4)' }}
                  onClick={() => playSong(song.id)}
                  data-testid={`song-card-${song.id}`}
                >
                  {/* Thumbnail */}
                  <div className="relative h-48 bg-muted overflow-hidden">
                    {song.thumbnail ? (
                      <img 
                        src={song.thumbnail} 
                        alt={song.title}
                        className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary/20 to-accent/20">
                        <Mic2 className="w-16 h-16 text-white/30" />
                      </div>
                    )}
                    {/* Play overlay */}
                    <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                      <button 
                        className="w-16 h-16 rounded-full bg-primary shadow-neon-pink flex items-center justify-center transform scale-0 group-hover:scale-100 transition-transform duration-300"
                        data-testid={`play-button-${song.id}`}
                      >
                        <svg className="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M8 5v14l11-7z" />
                        </svg>
                      </button>
                    </div>
                  </div>

                  {/* Info */}
                  <div className="p-6">
                    <h4 className="text-xl font-bold mb-2 line-clamp-1" data-testid={`song-title-${song.id}`}>
                      {song.title}
                    </h4>
                    <p className="text-muted-foreground line-clamp-1" data-testid={`song-artist-${song.id}`}>
                      {song.artist}
                    </p>
                    <div className="mt-4 flex items-center gap-2">
                      <span className="px-3 py-1 bg-primary/10 border border-primary/30 rounded-full text-xs font-mono uppercase tracking-wider text-primary">
                        {song.source_type}
                      </span>
                    </div>
                  </div>
                </motion.div>
              ))}
            </div>
          )}
        </div>
      </section>
    </div>
  );
};

export default Home;
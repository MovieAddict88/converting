import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Lock, User } from 'lucide-react';
import axios from 'axios';
import { motion } from 'framer-motion';

const BACKEND_URL = process.env.REACT_APP_BACKEND_URL;
const API = `${BACKEND_URL}/api`;

const AdminLogin = () => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await axios.post(`${API}/auth/login`, {
        username,
        password
      });

      localStorage.setItem('admin_token', response.data.token);
      localStorage.setItem('admin_username', response.data.username);
      navigate('/admin/dashboard');
    } catch (error) {
      setError(error.response?.data?.detail || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div 
      className="min-h-screen flex items-center justify-center p-6 relative overflow-hidden"
      data-testid="admin-login-page"
    >
      {/* Background */}
      <div 
        className="absolute inset-0 z-0"
        style={{
          backgroundImage: `url('https://images.unsplash.com/photo-1766339796353-86a05ecdeb0d?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTY2Njl8MHwxfHNlYXJjaHwxfHxhYnN0cmFjdCUyMG5lb24lMjBzb3VuZCUyMHdhdmVzfGVufDB8fHx8MTc2NzAzNjc1Mnww&ixlib=rb-4.1.0&q=85')`,
          backgroundSize: 'cover',
          backgroundPosition: 'center'
        }}
      >
        <div className="absolute inset-0 bg-black/70"></div>
      </div>

      {/* Login Card */}
      <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="relative z-10 w-full max-w-md backdrop-blur-xl rounded-2xl border border-white/10 p-8"
        style={{ background: 'rgba(0, 0, 0, 0.6)' }}
      >
        <div className="text-center mb-8">
          <h1 className="text-4xl font-unbounded font-black mb-2" data-testid="login-title">Admin Portal</h1>
          <p className="text-muted-foreground">Sign in to manage karaoke songs</p>
        </div>

        <form onSubmit={handleLogin} className="space-y-6">
          {error && (
            <div className="p-4 bg-error/10 border border-error/30 rounded-lg text-error text-sm" data-testid="error-message">
              {error}
            </div>
          )}

          <div>
            <label className="block text-sm font-medium mb-2" htmlFor="username">
              Username
            </label>
            <div className="relative">
              <User className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
              <input
                id="username"
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                className="w-full bg-input/50 border border-border focus:border-primary focus:ring-2 focus:ring-primary/50 rounded-lg text-white placeholder:text-muted-foreground/50 h-12 pl-12 pr-4 transition-all"
                placeholder="Enter username"
                required
                data-testid="username-input"
              />
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium mb-2" htmlFor="password">
              Password
            </label>
            <div className="relative">
              <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
              <input
                id="password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full bg-input/50 border border-border focus:border-primary focus:ring-2 focus:ring-primary/50 rounded-lg text-white placeholder:text-muted-foreground/50 h-12 pl-12 pr-4 transition-all"
                placeholder="Enter password"
                required
                data-testid="password-input"
              />
            </div>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full px-8 py-3 bg-primary text-white rounded-full font-bold tracking-wide uppercase shadow-neon-pink hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
            data-testid="login-button"
          >
            {loading ? 'Signing in...' : 'Sign In'}
          </button>
        </form>

        <div className="mt-6 text-center text-sm text-muted-foreground">
          Default: <code className="px-2 py-1 bg-muted/50 rounded">admin / admin123</code>
        </div>

        <button
          onClick={() => navigate('/')}
          className="mt-6 w-full text-center text-sm text-muted-foreground hover:text-white transition-colors"
          data-testid="back-to-home-link"
        >
          ← Back to Home
        </button>
      </motion.div>
    </div>
  );
};

export default AdminLogin;
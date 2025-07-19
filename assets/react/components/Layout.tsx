import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useUser } from '../context/UserContext';
import { useFlash } from '../context/FlashContext';
import './Layout.css';

const Layout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user, logout } = useUser();
  const { showFlash } = useFlash();
  const navigate = useNavigate();
  const location = useLocation();
  const [searchQuery, setSearchQuery] = useState('');
  const [followersCount, setFollowersCount] = useState<number>(0);
  const [followingsCount, setFollowingsCount] = useState<number>(0);

  useEffect(() => {
    if (!user) return;
    fetch(`/api/follows?followed=${user.id}`)
      .then(res => res.json())
      .then(data => {
        const arr = data['hydra:member'] || data.member || data;
        setFollowersCount(arr.length);
      });
    fetch(`/api/follows?follower=${user.id}`)
      .then(res => res.json())
      .then(data => {
        const arr = data['hydra:member'] || data.member || data;
        setFollowingsCount(arr.length);
      });
  }, [user]);

  const handleLogout = () => {
    logout();
    showFlash('success', 'Déconnexion réussie. À bientôt !');
    navigate('/login');
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/search?q=${encodeURIComponent(searchQuery.trim())}`);
    }
  };

  const isActive = (path: string) => {
    return location.pathname === path;
  };

  return (
    <div>
      {/* Header */}
      <header className="main-nav">
        <Link to="/" className="nav-logo">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="#2d5a27"/>
            <path d="M2 17L12 22L22 17" stroke="#2d5a27" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            <path d="M2 12L12 17L22 12" stroke="#2d5a27" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
          SymfoX
        </Link>
        <form className="search-form" onSubmit={handleSearch}>
          <input 
            type="text" 
            placeholder="Rechercher..." 
            className="search-input"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
          <button type="submit" className="search-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="11" cy="11" r="8" stroke="currentColor" strokeWidth="2"/>
              <path d="M21 21L16.65 16.65" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
          </button>
        </form>
      </header>
      
      {/* Layout principal */}
      <div className="layout">
        {/* Sidebar gauche */}
        <aside className="sidebar liquid-glass">
          <div className="sidebar-content">
            <div className="sidebar-logo">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="#2d5a27"/>
                <path d="M2 17L12 22L22 17" stroke="#2d5a27" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M2 12L12 17L22 12" stroke="#2d5a27" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </div>
            <nav className="sidebar-nav">
              <Link 
                to="/" 
                className={`nav-item ${isActive('/') ? 'active' : ''}`}
              >
                <span className="nav-item-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    <polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                </span>
                <span>Accueil</span>
              </Link>
              <Link 
                to="/suggested" 
                className={`nav-item ${isActive('/suggested') ? 'active' : ''}`}
              >
                <span className="nav-item-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" strokeWidth="2"/>
                    <path d="M21 21L16.65 16.65" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                  </svg>
                </span>
                <span>Suggestions</span>
              </Link>
              {user ? (
                <>
                  <Link 
                    to={`/profile/${user.id}`}
                    className={`nav-item ${location.pathname.startsWith('/profile') ? 'active' : ''}`}
                  >
                    <span className="nav-item-icon">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                        <circle cx="12" cy="7" r="4" stroke="currentColor" strokeWidth="2"/>
                      </svg>
                    </span>
                    <span>Profil</span>
                  </Link>
                  <button 
                    onClick={handleLogout}
                    className="nav-item"
                  >
                    <span className="nav-item-icon">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                        <polyline points="16,17 21,12 16,7" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                        <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                      </svg>
                    </span>
                    <span>Déconnexion</span>
                  </button>
                </>
              ) : (
                <>
                  <Link 
                    to="/login" 
                    className={`nav-item ${isActive('/login') ? 'active' : ''}`}
                  >
                    <span className="nav-item-icon">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H15" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                        <polyline points="10,17 15,12 10,7" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                        <line x1="15" y1="12" x2="3" y2="12" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                      </svg>
                    </span>
                    <span>Connexion</span>
                  </Link>
                  <Link 
                    to="/register" 
                    className={`nav-item ${isActive('/register') ? 'active' : ''}`}
                  >
                    <span className="nav-item-icon">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15C10.9391 15 9.92172 15.4214 9.17157 16.1716C8.42143 16.9217 8 17.9391 8 19V21" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                        <circle cx="12" cy="7" r="4" stroke="currentColor" strokeWidth="2"/>
                      </svg>
                    </span>
                    <span>Inscription</span>
                  </Link>
                </>
              )}
            </nav>
            {user && (
                <button 
                  className="tweet-btn"
                  onClick={() => navigate('/')}
                >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ marginRight: '8px' }}>
                  <path d="M12 5V19" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                  <path d="M5 12H19" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
                Publier
              </button>
            )}
          </div>
        </aside>

        {/* Colonne principale */}
        <main className="main">
          {children}
        </main>

        {/* Sidebar droite */}
        <aside className="trends liquid-glass">
          <div className="trends-section">
            <h3>Tendances</h3>
            <div className="trend-item">
              <Link to="/hashtag/symfony" className="trend-tag">#Symfony</Link>
            </div>
            <div className="trend-item">
              <Link to="/hashtag/europe" className="trend-tag">#Europe</Link>
            </div>
            <div className="trend-item">
              <Link to="/hashtag/rgpd" className="trend-tag">#RGPD</Link>
            </div>
            <div className="trend-item">
              <Link to="/hashtag/opensource" className="trend-tag">#OpenSource</Link>
            </div>
          </div>
        </aside>
      </div>
      
      {/* Footer */}
      <footer className="main-footer liquid-glass">
        <div className="footer-content">
          <div className="footer-links">
            <Link to="/legal">Mentions légales</Link>
            <Link to="/privacy">Politique de confidentialité</Link>
          </div>
          <div className="footer-copyright">
            © {new Date().getFullYear()} SymfoX - Réseau social éthique et conforme RGPD
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Layout; 
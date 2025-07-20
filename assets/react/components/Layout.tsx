import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useUser } from '../context/UserContext';
import './Layout.css';

const Layout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user, logout } = useUser();
  const location = useLocation();

  const isActive = (path: string) => location.pathname === path;

  return (
    <div className="layout-container">
      {/* Navigation Glass */}
      <nav className="glass-nav">
        <div className="nav-content">
          <Link to="/" className="nav-logo">
            <span className="logo-text">SymfoX</span>
          </Link>
          
          <div className="nav-links">
            <Link to="/" className={`nav-link ${isActive('/') ? 'active' : ''}`}>
              🏠 Accueil
            </Link>
            <Link to="/suggested" className={`nav-link ${isActive('/suggested') ? 'active' : ''}`}>
              💡 Suggestions
            </Link>
            <Link to="/search" className={`nav-link ${isActive('/search') ? 'active' : ''}`}>
              🔍 Recherche
            </Link>
            {user && (
              <Link to={`/profile/${user.id}`} className={`nav-link ${isActive(`/profile/${user.id}`) ? 'active' : ''}`}>
                👤 Profil
              </Link>
            )}
          </div>

          <div className="nav-auth">
            {user ? (
              <div className="user-menu">
                <span className="user-name">@{user.username}</span>
                <button onClick={logout} className="glass-button">
                  🚪 Déconnexion
                </button>
              </div>
            ) : (
              <div className="auth-buttons">
                <Link to="/login" className="glass-button">
                  🔑 Connexion
                </Link>
                <Link to="/register" className="glass-button primary">
                  ✨ Inscription
                </Link>
              </div>
            )}
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <main className="main-content">
        <div className="content-wrapper">
          {children}
        </div>
      </main>

      {/* Footer Glass */}
      <footer className="glass-footer">
        <div className="footer-content">
          <div className="footer-links">
            <Link to="/legal/mentions" className="footer-link">Mentions légales</Link>
            <Link to="/legal/privacy" className="footer-link">Politique de confidentialité</Link>
          </div>
          <div className="footer-copyright">
            © 2024 SymfoX - Réseau social moderne
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Layout; 
import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useUser } from '../context/UserContext';
import { useFlash } from '../context/FlashContext';
import './Auth.css';

const LoginPage: React.FC = () => {
  console.log('DEBUG LOGIN PAGE - Component rendered, user:', null);
  
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const { login } = useUser();
  const { showFlash } = useFlash();
  const navigate = useNavigate();

  useEffect(() => {
    console.log('DEBUG LOGIN PAGE - useEffect triggered, user:', null);
  }, []);

  useEffect(() => {
    // Si on arrive sur /login sans token, afficher le message session expirée
    if (!localStorage.getItem('jwt_token')) {
      showFlash('error', 'Session expirée, veuillez vous reconnecter.');
    }
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    console.log('DEBUG LOGIN PAGE - Form submitted');
    console.log('DEBUG LOGIN PAGE - Calling login function');
    
    setLoading(true);
    setError(null);
    
    try {
      await login(email, password);
      console.log('DEBUG LOGIN PAGE - Login successful');
      showFlash('success', 'Connexion réussie !');
      navigate('/');
    } catch (err: any) {
      console.log('DEBUG LOGIN PAGE - Login failed:', err.message);
      setError(err.message || 'Erreur lors de la connexion');
      showFlash('error', err.message || 'Erreur lors de la connexion');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="auth-container">
      <form className="auth-form liquid-glass" onSubmit={handleSubmit}>
        <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ width: '64px', height: '64px', marginBottom: '1rem' }}>
            <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="#2d5a27"/>
            <path d="M2 17L12 22L22 17" stroke="#2d5a27" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            <path d="M2 12L12 17L22 12" stroke="#2d5a27" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
          <h1>Connexion</h1>
          <p style={{ color: 'rgba(45, 90, 39, 0.7)', margin: 0 }}>Bienvenue sur SymfoX</p>
        </div>
        
        {error && (
          <div className="flash-message error" style={{ marginBottom: '1.5rem' }}>
            {error}
          </div>
        )}
        
        <div className="form-group">
          <label className="form-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ marginRight: '0.5rem' }}>
              <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              <polyline points="22,6 12,13 2,6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
            Email
          </label>
          <input
            type="email"
            placeholder="votre@email.com"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="form-input"
            required
            disabled={loading}
          />
        </div>
        
        <div className="form-group">
          <label className="form-label">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ marginRight: '0.5rem' }}>
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" strokeWidth="2"/>
              <circle cx="12" cy="16" r="1" stroke="currentColor" strokeWidth="2"/>
              <path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" strokeWidth="2"/>
            </svg>
            Mot de passe
          </label>
          <input
            type="password"
            placeholder="Votre mot de passe"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="form-input"
            required
            disabled={loading}
          />
        </div>
        
        <button 
          type="submit" 
          className="form-button" 
          disabled={loading || !email || !password}
        >
          {loading ? (
            <>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ marginRight: '0.5rem', animation: 'spin 1s linear infinite' }}>
                <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="2" strokeOpacity="0.3"/>
                <path d="M12 2C6.47715 2 2 6.47715 2 12" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
              </svg>
              Connexion...
            </>
          ) : (
            <>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ marginRight: '0.5rem' }}>
                <path d="M15 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H15" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <polyline points="10,17 15,12 10,7" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <line x1="15" y1="12" x2="3" y2="12" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              Se connecter
            </>
          )}
        </button>
        
        <div className="auth-divider">ou</div>
        
        <div className="social-auth">
          <button type="button" className="social-button">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" strokeWidth="2"/>
              <path d="M8 12H16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              <path d="M12 8V16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            </svg>
            Continuer avec Google
          </button>
        </div>
        
        <p style={{ textAlign: 'center', marginTop: '2rem' }}>
          <Link to="/register" className="auth-link">
            Pas de compte ? S'inscrire
          </Link>
        </p>
      </form>
    </div>
  );
};

export default LoginPage; 
import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { register } from '../api/auth';
import { useFlash } from '../context/FlashContext';
import { useLoader } from '../context/LoaderContext';
import { useUser } from '../context/UserContext';
import './Auth.css';

const RegisterPage: React.FC = () => {
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [password2, setPassword2] = useState('');
  const [agree, setAgree] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const navigate = useNavigate();
  const { showFlash } = useFlash();
  const { showLoader, hideLoader } = useLoader();
  const { user } = useUser();

  useEffect(() => {
    if (user) {
      navigate('/');
    }
  }, [user, navigate]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setSuccess(null);
    if (password !== password2) {
      setError('Les mots de passe ne correspondent pas');
      showFlash('error', 'Les mots de passe ne correspondent pas');
      return;
    }
    if (!agree) {
      setError('Vous devez accepter les conditions d\'utilisation');
      showFlash('error', 'Vous devez accepter les conditions d\'utilisation');
      return;
    }
    setLoading(true);
    showLoader();
    try {
      await register(username, email, password);
      setSuccess('Inscription réussie ! Vous pouvez vous connecter.');
      showFlash('success', 'Inscription réussie ! Vous pouvez vous connecter.');
      setTimeout(() => navigate('/login'), 1500);
    } catch (err: any) {
      setError(err.message || 'Erreur inconnue');
      showFlash('error', err.message || 'Erreur inconnue');
    } finally {
      setLoading(false);
      hideLoader();
    }
  };

  return (
    <div className="auth-container" style={{ color: '#fff' }}>
      <form className="auth-form liquid-glass" onSubmit={handleSubmit} style={{ color: '#fff' }}>
        <div style={{ textAlign: 'center', marginBottom: '2rem', color: '#fff' }}>
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ width: '64px', height: '64px', marginBottom: '1rem' }}>
            <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="#2d5a27"/>
            <path d="M2 17L12 22L22 17" stroke="#2d5a27" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
            <path d="M2 12L12 17L22 12" stroke="#2d5a27" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
          <h1 style={{ color: '#fff' }}>Rejoindre SymfoX</h1>
          <p style={{ color: '#fff', margin: 0 }}>Créez votre compte éthique</p>
        </div>
        
        {error && (
          <div className="flash-message error" style={{ marginBottom: '1.5rem' }}>
            {error}
          </div>
        )}
        
        {success && (
          <div className="flash-message success" style={{ marginBottom: '1.5rem' }}>
            {success}
          </div>
        )}
        
        <div className="form-group" style={{ color: '#fff' }}>
          <label className="form-label" style={{ color: '#fff' }}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ marginRight: '0.5rem' }}>
              <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              <circle cx="12" cy="7" r="4" stroke="currentColor" strokeWidth="2"/>
            </svg>
            Nom d'utilisateur
          </label>
          <input
            type="text"
            placeholder="Votre nom d'utilisateur"
            value={username}
            onChange={e => setUsername(e.target.value)}
            required
            disabled={loading}
            className="form-input"
          />
        </div>
        
        <div className="form-group" style={{ color: '#fff' }}>
          <label className="form-label" style={{ color: '#fff' }}>
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
            onChange={e => setEmail(e.target.value)}
            required
            disabled={loading}
            className="form-input"
          />
        </div>
        
        <div className="form-group" style={{ color: '#fff' }}>
          <label className="form-label" style={{ color: '#fff' }}>
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
            onChange={e => setPassword(e.target.value)}
            required
            disabled={loading}
            className="form-input"
          />
        </div>
        
        <div className="form-group" style={{ color: '#fff' }}>
          <label className="form-label" style={{ color: '#fff' }}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ marginRight: '0.5rem' }}>
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" strokeWidth="2"/>
              <circle cx="12" cy="16" r="1" stroke="currentColor" strokeWidth="2"/>
              <path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" strokeWidth="2"/>
            </svg>
            Confirmer le mot de passe
          </label>
          <input
            type="password"
            placeholder="Répétez votre mot de passe"
            value={password2}
            onChange={e => setPassword2(e.target.value)}
            required
            disabled={loading}
            className="form-input"
          />
        </div>
        
        <div className="form-group" style={{ color: '#fff' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', color: '#fff' }}>
            <input
              type="checkbox"
              id="agreeTerms"
              checked={agree}
              onChange={e => setAgree(e.target.checked)}
              required
              disabled={loading}
              style={{
                width: '18px',
                height: '18px',
                accentColor: '#2d5a27',
                cursor: loading ? 'not-allowed' : 'pointer'
              }}
            />
            <label htmlFor="agreeTerms" style={{ 
              margin: 0, 
              fontSize: '0.9rem',
              color: '#fff',
              cursor: loading ? 'not-allowed' : 'pointer'
            }}>
              J'accepte les{' '}
              <Link to="/legal" style={{ color: '#fff', textDecoration: 'none' }}>
                conditions d'utilisation
              </Link>
            </label>
          </div>
        </div>
        
        <button 
          type="submit" 
          className="form-button" 
          disabled={loading || !username || !email || !password || !password2 || !agree}
          style={{ color: '#fff' }}
        >
          {loading ? (
            <>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ marginRight: '0.5rem', animation: 'spin 1s linear infinite' }}>
                <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="2" strokeOpacity="0.3"/>
                <path d="M12 2C6.47715 2 2 6.47715 2 12" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
              </svg>
              Inscription...
            </>
          ) : (
            <>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ marginRight: '0.5rem' }}>
                <path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15C10.9391 15 9.92172 15.4214 9.17157 16.1716C8.42143 16.9217 8 17.9391 8 19V21" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <circle cx="12" cy="7" r="4" stroke="currentColor" strokeWidth="2"/>
              </svg>
              S'inscrire
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
        
        <p style={{ textAlign: 'center', marginTop: '2rem', color: '#fff' }}>
          <Link to="/login" className="auth-link" style={{ color: '#fff' }}>
            Déjà un compte ? Se connecter
          </Link>
        </p>
      </form>
    </div>
  );
};

export default RegisterPage; 
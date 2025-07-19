import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useUser } from '../context/UserContext';
import { getToken } from '../api/auth';

const ProfileEditPage: React.FC = () => {
  const { user, refresh } = useUser();
  const [username, setUsername] = useState(user?.username || '');
  const [email, setEmail] = useState(user?.email || '');
  const [password, setPassword] = useState('');
  const [password2, setPassword2] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const navigate = useNavigate();

  useEffect(() => {
    setUsername(user?.username || '');
    setEmail(user?.email || '');
  }, [user]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    
    // Vérifier que les mots de passe correspondent si un nouveau mot de passe est fourni
    if (password && password !== password2) {
      setError('Les mots de passe ne correspondent pas');
      return;
    }
    
    setLoading(true);
    try {
      // Préparer les données à envoyer
      const updateData: any = { username, email };
      
      // N'ajouter le mot de passe que s'il est fourni et non vide
      if (password && password.trim() !== '') {
        updateData.password = password;
      }
      
      console.log('DEBUG PROFILE EDIT - Sending update data:', updateData);
      
      const res = await fetch(`/api/profile/update`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${getToken()}`,
        },
        body: JSON.stringify(updateData),
      });
      
      console.log('DEBUG PROFILE EDIT - Response status:', res.status);
      
      if (!res.ok) {
        const errorData = await res.json().catch(() => ({}));
        console.error('DEBUG PROFILE EDIT - Error response:', errorData);
        throw new Error(errorData.detail || 'Erreur lors de la modification du profil');
      }
      
      console.log('DEBUG PROFILE EDIT - Profile updated successfully');
      refresh();
      navigate(`/profile/${user?.id}`);
    } catch (err: any) {
      console.error('DEBUG PROFILE EDIT - Error:', err);
      setError(err.message || 'Erreur inconnue');
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <div className="main-header">
        <h1>Modifier le profil</h1>
      </div>
      <div className="auth-container">
        <div className="auth-form">
          <form onSubmit={handleSubmit}>
            <div className="form-group">
              <label>Nom d'utilisateur</label>
              <input className="form-control" type="text" value={username} onChange={e => setUsername(e.target.value)} required disabled={loading} />
            </div>
            <div className="form-group">
              <label>Email</label>
              <input className="form-control" type="email" value={email} onChange={e => setEmail(e.target.value)} required disabled={loading} />
            </div>
            <div className="form-group">
              <label>Nouveau mot de passe</label>
              <input className="form-control" type="password" value={password} onChange={e => setPassword(e.target.value)} disabled={loading} />
            </div>
            <div className="form-group">
              <label>Répéter le mot de passe</label>
              <input className="form-control" type="password" value={password2} onChange={e => setPassword2(e.target.value)} disabled={loading} />
            </div>
            <button type="submit" className="btn" disabled={loading}>{loading ? 'Sauvegarde...' : 'Sauvegarder'}</button>
            {error && <div style={{ color: 'red', marginTop: 8 }}>{error}</div>}
          </form>
          <div className="form-links" style={{ marginTop: 16 }}>
            <a href={`/profile/${user?.id}`}>Retour au profil</a>
          </div>
        </div>
      </div>
    </>
  );
};

export default ProfileEditPage; 
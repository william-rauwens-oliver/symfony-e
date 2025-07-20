import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useUser } from '../context/UserContext';
import { getToken } from '../api/auth';

const ProfileDeletePage: React.FC = () => {
  const { user, logout } = useUser();
  const navigate = useNavigate();

  const handleDelete = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!window.confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) return;
    try {
      await fetch(`/api/users/${user?.id}`, {
        method: 'DELETE',
        headers: {
          Authorization: `Bearer ${getToken()}`,
        },
      });
      logout();
      navigate('/');
    } catch (err) {
      alert('Erreur lors de la suppression du compte');
    }
  };

  return (
    <div className="profile-delete-container">
      <div className="glass-header">
        <h1>🗑️ Supprimer mon compte</h1>
        <p>Action irréversible - Réfléchissez bien</p>
      </div>
      
      <div className="glass-form">
        <div className="glass-card" style={{
          background: 'rgba(255, 59, 48, 0.1)',
          borderColor: 'rgba(255, 59, 48, 0.2)',
          marginBottom: '24px',
          textAlign: 'center',
          padding: '24px'
        }}>
          <div style={{fontSize: '3rem', marginBottom: '16px'}}>⚠️</div>
          <h3 style={{color: '#FF3B30', marginBottom: '12px'}}>Attention !</h3>
          <p style={{color: 'var(--text-secondary)', lineHeight: '1.6'}}>
            Êtes-vous absolument sûr de vouloir supprimer votre compte ?<br />
            <strong style={{color: '#FF3B30'}}>Cette action est irréversible.</strong>
          </p>
          <ul style={{
            textAlign: 'left',
            marginTop: '16px',
            color: 'var(--text-secondary)',
            paddingLeft: '20px'
          }}>
            <li>Toutes vos publications seront supprimées</li>
            <li>Tous vos commentaires seront supprimés</li>
            <li>Tous vos likes seront supprimés</li>
            <li>Votre profil sera définitivement effacé</li>
          </ul>
        </div>
        
        <form onSubmit={handleDelete}>
          <div className="form-actions">
            <button 
              type="submit" 
              className="glass-button" 
              style={{
                background: 'rgba(255, 59, 48, 0.9)',
                borderColor: 'rgba(255, 59, 48, 0.3)',
                color: 'white',
                fontSize: '1.1rem',
                padding: '12px 24px'
              }}
            >
              💀 Oui, supprimer définitivement mon compte
            </button>
            
            <a 
              href={`/profile/${user?.id}`} 
              className="glass-button"
              style={{
                background: 'rgba(255, 255, 255, 0.9)',
                color: 'var(--text-primary)',
                fontSize: '1.1rem',
                padding: '12px 24px'
              }}
            >
              🔙 Annuler et retourner au profil
            </a>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ProfileDeletePage; 
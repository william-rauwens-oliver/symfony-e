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
    <>
      <div className="main-header">
        <h1>Supprimer mon compte</h1>
      </div>
      <div className="auth-container">
        <div className="auth-form">
          <p>
            Êtes-vous sûr de vouloir supprimer votre compte ? <br /><strong>Cette action est irréversible.</strong>
          </p>
          <form onSubmit={handleDelete}>
            <button type="submit" className="btn" style={{ background: '#e0245e' }}>Oui, supprimer mon compte</button>
            <a href={`/profile/${user?.id}`} className="btn" style={{ background: '#657786', marginLeft: 10 }}>Annuler</a>
          </form>
        </div>
      </div>
    </>
  );
};

export default ProfileDeletePage; 
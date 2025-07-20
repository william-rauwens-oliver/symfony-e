import React, { useState } from 'react';
import { fetchWithAuth } from '../api/auth';

interface PublicationDeleteButtonProps {
  publicationId: number;
  onDelete?: () => void;
}

const PublicationDeleteButton: React.FC<PublicationDeleteButtonProps> = ({
  publicationId,
  onDelete
}) => {
  const [loading, setLoading] = useState(false);

  const handleDelete = async () => {
    if (!window.confirm('Êtes-vous sûr de vouloir supprimer cette publication ? Cette action est irréversible.')) {
      return;
    }

    setLoading(true);
    try {
      const response = await fetchWithAuth(`/api/publications/${publicationId}`, {
        method: 'DELETE'
      });

      if (response.ok) {
        console.log('✅ Publication supprimée avec succès');
        if (onDelete) {
          onDelete();
        }
      } else {
        const errorData = await response.json();
        console.error('❌ Erreur lors de la suppression:', errorData);
        alert('Erreur lors de la suppression de la publication');
      }
    } catch (error) {
      console.error('❌ Erreur lors de la suppression:', error);
      alert('Erreur lors de la suppression de la publication');
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      className="publication-delete-btn"
      onClick={handleDelete}
      disabled={loading}
      title="Supprimer cette publication"
      style={{
        background: 'rgba(255, 59, 48, 0.1)',
        border: '1px solid rgba(255, 59, 48, 0.2)',
        color: '#FF3B30',
        padding: '8px 12px',
        borderRadius: '8px',
        cursor: 'pointer',
        fontSize: '0.9rem',
        fontWeight: '500',
        transition: 'all 0.2s ease'
      }}
    >
      {loading ? '⏳ Suppression...' : '🗑️ Supprimer'}
    </button>
  );
};

export default PublicationDeleteButton; 
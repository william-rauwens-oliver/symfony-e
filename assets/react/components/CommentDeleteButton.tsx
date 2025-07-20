import React, { useState } from 'react';
import { fetchWithAuth } from '../api/auth';

interface CommentDeleteButtonProps {
  commentId: number;
  onDelete?: () => void;
}

const CommentDeleteButton: React.FC<CommentDeleteButtonProps> = ({
  commentId,
  onDelete
}) => {
  const [loading, setLoading] = useState(false);

  const handleDelete = async () => {
    if (!window.confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ? Cette action est irréversible.')) {
      return;
    }

    setLoading(true);
    try {
      const response = await fetchWithAuth(`/api/commentaires/${commentId}`, {
        method: 'DELETE'
      });

      if (response.ok) {
        console.log('✅ Commentaire supprimé avec succès');
        if (onDelete) {
          onDelete();
        }
      } else {
        const errorData = await response.json();
        console.error('❌ Erreur lors de la suppression:', errorData);
        alert('Erreur lors de la suppression du commentaire');
      }
    } catch (error) {
      console.error('❌ Erreur lors de la suppression:', error);
      alert('Erreur lors de la suppression du commentaire');
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      className="comment-delete-btn"
      onClick={handleDelete}
      disabled={loading}
      title="Supprimer ce commentaire"
      style={{
        background: 'rgba(255, 59, 48, 0.1)',
        border: '1px solid rgba(255, 59, 48, 0.2)',
        color: '#FF3B30',
        padding: '4px 8px',
        borderRadius: '6px',
        cursor: 'pointer',
        fontSize: '0.8rem',
        fontWeight: '500',
        transition: 'all 0.2s ease',
        marginLeft: '8px'
      }}
    >
      {loading ? '⏳' : '🗑️'}
    </button>
  );
};

export default CommentDeleteButton; 
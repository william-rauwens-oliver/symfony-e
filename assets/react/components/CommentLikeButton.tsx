import React, { useState, useEffect } from 'react';
import { fetchWithAuth } from '../api/auth';
import { getToken } from '../api/auth';

interface CommentLikeButtonProps {
  commentId: number;
  initialCount: number;
  initiallyLiked: boolean;
  onLike?: () => void;
}

const CommentLikeButton: React.FC<CommentLikeButtonProps> = ({
  commentId,
  initialCount,
  initiallyLiked,
  onLike
}) => {
  const [likeCount, setLikeCount] = useState(initialCount);
  const [isLiked, setIsLiked] = useState(initiallyLiked);
  const [userLikeId, setUserLikeId] = useState<number | null>(null);
  const [loading, setLoading] = useState(false);

  // Récupérer le statut du like au montage du composant
  useEffect(() => {
    const fetchLikeStatus = async () => {
      try {
        const response = await fetchWithAuth(`/api/commentaires/${commentId}/like-status`);
        if (response.ok) {
          const data = await response.json();
          setLikeCount(data.likeCount);
          setIsLiked(data.likedByCurrentUser);
          setUserLikeId(data.userLikeId);
          console.log('📊 Statut like récupéré pour commentaire', commentId, ':', data);
        }
      } catch (error) {
        console.error('Erreur lors de la récupération du statut like:', error);
      }
    };

    fetchLikeStatus();
  }, [commentId]);

  const handleLike = async () => {
    if (loading) return;

    setLoading(true);
    const token = getToken();
    
    if (!token) {
      console.error('❌ Pas de token pour liker le commentaire');
      setLoading(false);
      return;
    }

    try {
      if (isLiked && userLikeId) {
        // Unlike - utiliser l'ID du like directement
        console.log('🔄 Unlike du commentaire:', commentId, 'avec like ID:', userLikeId);
        const deleteResponse = await fetchWithAuth(`/api/comment_likes/${userLikeId}`, {
          method: 'DELETE'
        });

        if (deleteResponse.ok) {
          setLikeCount(prev => Math.max(0, prev - 1));
          setIsLiked(false);
          setUserLikeId(null);
          console.log('✅ Commentaire unliked avec succès');
        } else {
          const errorData = await deleteResponse.json();
          console.error('❌ Erreur lors de l\'unlike:', errorData);
        }
      } else {
        // Like
        console.log('🔄 Like du commentaire:', commentId);
        const response = await fetchWithAuth('/api/comment_likes', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            commentaire: `/api/commentaires/${commentId}`
          })
        });

        if (response.ok) {
          const newLike = await response.json();
          setLikeCount(prev => prev + 1);
          setIsLiked(true);
          setUserLikeId(newLike.id);
          console.log('✅ Commentaire liked avec succès, like ID:', newLike.id);
        } else {
          const errorData = await response.json();
          console.error('❌ Erreur lors du like:', errorData);
        }
      }

      if (onLike) {
        onLike();
      }
    } catch (error) {
      console.error('❌ Erreur lors du like/unlike du commentaire:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      className={`comment-like-btn ${isLiked ? 'liked' : ''}`}
      onClick={handleLike}
      disabled={loading}
      title={isLiked ? 'Ne plus aimer ce commentaire' : 'Aimer ce commentaire'}
    >
      <span className="comment-like-icon">
        {isLiked ? '❤️' : '🤍'}
      </span>
      <span className="comment-like-count">
        {likeCount > 0 ? likeCount : ''}
      </span>
    </button>
  );
};

export default CommentLikeButton; 
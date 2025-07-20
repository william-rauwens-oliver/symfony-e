import React, { useState, useEffect } from 'react';
import { fetchWithAuth, getToken } from '../api/auth';
import CommentLikeButton from './CommentLikeButton';
import CommentDeleteButton from './CommentDeleteButton';
import { useUser } from '../context/UserContext';

interface Comment {
  id: number;
  content: string;
  createdAt: string;
  user: {
    id: number;
    username: string;
  } | string;
  publication?: {
    id: number;
  } | string;
  replies?: Comment[] | string[];
  parent?: number;
  likeCount?: number;
  likedByCurrentUser?: boolean;
}

interface CommentListProps {
  publicationId: number;
}

const CommentList: React.FC<CommentListProps> = ({ publicationId }) => {
  const [comments, setComments] = useState<Comment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [newComment, setNewComment] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const { user } = useUser();

  const fetchComments = async () => {
    try {
      setLoading(true);
      console.log('DEBUG COMMENT LIST - Fetching comments for publicationId:', publicationId);
      
      const response = await fetchWithAuth(`/api/commentaires-with-likes?publication=${publicationId}`);
      const allComments = await response.json();
      
      console.log('DEBUG COMMENT LIST - All comments received:', allComments);
      console.log('DEBUG COMMENT LIST - Comments count:', allComments.length);
      
      // Filtrer pour ne garder que les commentaires parents (sans parent) ET de la bonne publication
      const parentComments = allComments.filter((comment: Comment) => {
        const isParent = !comment.parent;
        const belongsToPublication = comment.publication && 
          (typeof comment.publication === 'string' ? 
            comment.publication.includes(`/api/publications/${publicationId}`) : 
            comment.publication.id === publicationId);
        
        console.log('DEBUG COMMENT LIST - Comment:', comment.id, 'isParent:', isParent, 'belongsToPublication:', belongsToPublication);
        
        return isParent && belongsToPublication;
      });
      
      console.log('DEBUG COMMENT LIST - Parent comments after filter:', parentComments);
      console.log('DEBUG COMMENT LIST - Parent comments count:', parentComments.length);
      
      // Récupérer les données complètes des utilisateurs pour chaque commentaire
      const commentsWithUsers = await Promise.all(
        parentComments.map(async (comment: Comment) => {
          if (typeof comment.user === 'string' && comment.user.startsWith('/api/')) {
            try {
              const userId = comment.user.split('/').pop();
              const userResponse = await fetchWithAuth(`/api/users/${userId}`);
              const userData = await userResponse.json();
              console.log('DEBUG USER - Données utilisateur récupérées:', userData);
              return { ...comment, user: userData };
            } catch (err) {
              console.error('DEBUG USER - Erreur récupération utilisateur:', err);
              return comment;
            }
          }
          return comment;
        })
      );
      
      console.log('DEBUG COMMENTS - Commentaires avec utilisateurs:', commentsWithUsers);
      
      setComments(commentsWithUsers);
    } catch (err) {
      console.error('Erreur lors du chargement des commentaires:', err);
      setError('Erreur lors du chargement des commentaires');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchComments();
  }, [publicationId]);

  const handleSubmitComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newComment.trim()) return;

    try {
      setSubmitting(true);
      const token = getToken();
      if (!token) {
        alert('Vous devez être connecté pour commenter');
        return;
      }

      const response = await fetchWithAuth('/api/commentaires', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          content: newComment,
          publication: `/api/publications/${publicationId}`,
        }),
      });

      if (response.ok) {
        setNewComment('');
        await fetchComments(); // Recharger les commentaires
      } else {
        throw new Error('Erreur lors de l\'ajout du commentaire');
      }
    } catch (err) {
      console.error('Erreur:', err);
      alert('Erreur lors de l\'ajout du commentaire');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <div className="comments-section">
        <h3>💬 Commentaires</h3>
        <div className="glass-loading">
          <div className="loading-spinner"></div>
          <p style={{marginLeft: '12px', color: 'var(--text-secondary)'}}>Chargement des commentaires...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="comments-section">
        <h3>💬 Commentaires</h3>
        <div className="glass-error">
          ❌ Erreur: {error}
        </div>
      </div>
    );
  }

  return (
    <div className="comments-section">
      <h3>💬 Commentaires</h3>
      
      {/* Formulaire pour ajouter un nouveau commentaire */}
      <form onSubmit={handleSubmitComment} className="comment-form">
        <textarea
          value={newComment}
          onChange={(e) => setNewComment(e.target.value)}
          placeholder="Partagez votre pensée..."
          rows={3}
          disabled={submitting}
        />
        <button 
          type="submit" 
          disabled={submitting || !newComment.trim()}
        >
          {submitting ? '⏳ Envoi...' : '✨ Publier le commentaire'}
        </button>
      </form>

      {/* Liste des commentaires */}
      <div className="comments-list">
        {comments.length === 0 ? (
          <div className="glass-card" style={{textAlign: 'center', padding: '32px'}}>
            <p style={{color: 'var(--text-secondary)', fontSize: '1rem'}}>
              💭 Aucun commentaire pour le moment. Soyez le premier à commenter !
            </p>
          </div>
        ) : (
          comments.map((comment) => (
            <CommentItem 
              key={`${publicationId}-${comment.id}`} 
              comment={comment} 
              onCommentAdded={fetchComments}
              publicationId={publicationId}
              currentUserId={user?.id}
            />
          ))
        )}
      </div>
    </div>
  );
};

interface CommentItemProps {
  comment: Comment;
  onCommentAdded: () => void;
  publicationId: number;
  currentUserId?: number;
}

const CommentItem: React.FC<CommentItemProps> = ({ comment, onCommentAdded, publicationId, currentUserId }) => {
  const [showReplies, setShowReplies] = useState(false);
  const [replies, setReplies] = useState<Comment[]>([]);
  const [replyCount, setReplyCount] = useState<number>(0);
  const [showReplyForm, setShowReplyForm] = useState(false);
  const [replyContent, setReplyContent] = useState('');
  const [submittingReply, setSubmittingReply] = useState(false);
  const [loadingReplies, setLoadingReplies] = useState(false);

  // Fonction pour récupérer le nombre de réponses
  const fetchReplyCount = async () => {
    try {
      const response = await fetchWithAuth(`/api/commentaires-with-likes?publication=${publicationId}&parent=${comment.id}`);
      const repliesData = await response.json();
      setReplyCount(repliesData.length);
      console.log('📊 Nombre de réponses pour le commentaire', comment.id, ':', repliesData.length);
    } catch (err) {
      console.error('Erreur lors du comptage des réponses:', err);
      setReplyCount(0);
    }
  };

  // Charger le nombre de réponses au montage du composant
  useEffect(() => {
    fetchReplyCount();
  }, [comment.id, publicationId]);

  const handleShowReplies = async () => {
    console.log('🔄 handleShowReplies appelé pour le commentaire:', comment.id);
    console.log('🔄 showReplies actuel:', showReplies);
    console.log('🔄 replies.length actuel:', replies.length);
    console.log('🔄 comment.replies:', comment.replies);
    
    if (!showReplies) {
      // Si les réponses ne sont pas encore chargées
      if (replies.length === 0) {
        console.log('🔄 Chargement des réponses depuis l\'API...');
        setLoadingReplies(true);
        try {
          // Vérifier d'abord si les réponses sont déjà dans le commentaire
          if (comment.replies && Array.isArray(comment.replies) && comment.replies.length > 0) {
            console.log('DEBUG REPLIES - Réponses du commentaire (références):', comment.replies);
            
            // Si ce sont des références d'API, les récupérer individuellement
            if (typeof comment.replies[0] === 'string' && (comment.replies[0] as string).startsWith('/api/')) {
              console.log('DEBUG REPLIES - Récupération des données complètes des réponses...');
              const fullReplies = [];
              
              for (const replyRef of comment.replies) {
                try {
                  const replyId = (replyRef as unknown as string).split('/').pop(); // Extraire l'ID de l'URL
                  const response = await fetchWithAuth(`/api/commentaires/${replyId}`);
                  const replyData = await response.json();
                  console.log('DEBUG REPLIES - Réponse complète récupérée:', replyData);
                  
                  // Récupérer les données utilisateur complètes pour la réponse
                  if (typeof replyData.user === 'string' && replyData.user.startsWith('/api/')) {
                    try {
                      const userId = replyData.user.split('/').pop();
                      const userResponse = await fetchWithAuth(`/api/users/${userId}`);
                      const userData = await userResponse.json();
                      console.log('DEBUG REPLIES - Utilisateur de la réponse récupéré:', userData);
                      replyData.user = userData;
                    } catch (err) {
                      console.error('DEBUG REPLIES - Erreur récupération utilisateur de la réponse:', err);
                    }
                  }
                  
                  fullReplies.push(replyData);
                } catch (err) {
                  console.error('DEBUG REPLIES - Erreur récupération réponse:', err);
                }
              }
              
              setReplies(fullReplies);
            } else {
              // Si ce sont déjà des objets complets
              setReplies(comment.replies as Comment[]);
            }
          } else {
            // Sinon, récupérer depuis l'API
            console.log('🔄 Appel API pour récupérer les réponses...');
            const response = await fetchWithAuth(`/api/commentaires-with-likes?publication=${publicationId}&parent=${comment.id}`);
            const repliesData = await response.json();
            console.log('DEBUG REPLIES - Réponses récupérées depuis API:', repliesData);
            console.log('DEBUG REPLIES - Structure d\'une réponse:', repliesData[0]);
            setReplies(repliesData);
            setReplyCount(repliesData.length);
          }
        } catch (err) {
          console.error('Erreur lors du chargement des réponses:', err);
        } finally {
          setLoadingReplies(false);
        }
      } else {
        console.log('🔄 Réponses déjà chargées, pas besoin de recharger');
      }
    }
    setShowReplies(!showReplies);
  };

  const handleSubmitReply = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!replyContent.trim()) return;

    try {
      setSubmittingReply(true);
      const token = getToken();
      if (!token) {
        alert('Vous devez être connecté pour répondre');
        return;
      }

      const response = await fetchWithAuth('/api/commentaires', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          content: replyContent,
          publication: `/api/publications/${publicationId}`,
          parent: `/api/commentaires/${comment.id}`,
        }),
      });

      if (response.ok) {
        setReplyContent('');
        setShowReplyForm(false);
        
        // Recharger les réponses de ce commentaire spécifiquement
        try {
          const responseReplies = await fetchWithAuth(`/api/commentaires-with-likes?publication=${publicationId}&parent=${comment.id}`);
          const repliesData = await responseReplies.json();
          setReplies(repliesData);
          setReplyCount(repliesData.length);
          
          // Forcer l'affichage des réponses
          setShowReplies(true);
          
          console.log('✅ Réponse ajoutée et réponses rechargées:', repliesData);
          console.log('📊 Nouveau nombre de réponses:', repliesData.length);
        } catch (err) {
          console.error('Erreur lors du rechargement des réponses:', err);
        }
        
        // Recharger aussi tous les commentaires pour mettre à jour le compteur
        await onCommentAdded();
      } else {
        throw new Error('Erreur lors de l\'ajout de la réponse');
      }
    } catch (err) {
      console.error('Erreur:', err);
      alert('Erreur lors de l\'ajout de la réponse');
    } finally {
      setSubmittingReply(false);
    }
  };

  const formatDate = (dateString: string) => {
    if (!dateString) {
      return 'Date inconnue';
    }
    
    const date = new Date(dateString);
    
    // Vérifier si la date est valide
    if (isNaN(date.getTime())) {
      console.error('DEBUG DATE - Date invalide:', dateString);
      return 'Date inconnue';
    }
    
    try {
      return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    } catch (err) {
      console.error('DEBUG DATE - Erreur formatage:', err, dateString);
      return 'Date inconnue';
    }
  };

  const getInitials = (username: string) => {
    if (!username || typeof username !== 'string') {
      return '??';
    }
    return username.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
  };

  return (
    <div className="glass-comment">
      <div className="comment-header">
        <div className="comment-avatar">
          {getInitials(typeof comment.user === 'object' ? comment.user.username : 'Utilisateur')}
        </div>
        <div className="comment-meta">
          <p className="comment-author">@{typeof comment.user === 'object' ? comment.user.username : 'utilisateur'}</p>
          <p className="comment-date">{formatDate(comment.createdAt)}</p>
        </div>
      </div>
      
      <div className="comment-content">
        {comment.content}
      </div>
      
      <div className="comment-actions">
        <CommentLikeButton
          commentId={comment.id}
          initialCount={comment.likeCount || 0}
          initiallyLiked={comment.likedByCurrentUser || false}
          onLike={onCommentAdded}
        />
        
        <button 
          className="comment-action-btn"
          onClick={(e) => {
            e.preventDefault();
            setShowReplyForm(!showReplyForm);
          }}
        >
          💬 Répondre
        </button>
        
        {replyCount > 0 && (
          <button 
            className="comment-action-btn"
            onClick={(e) => {
              e.preventDefault();
              handleShowReplies();
            }}
          >
            {showReplies ? '👆' : '👇'} {showReplies ? 'Masquer' : 'Afficher'} {replyCount} réponse{replyCount > 1 ? 's' : ''}
          </button>
        )}
        
        {/* Bouton de suppression pour nos propres commentaires */}
        {currentUserId && typeof comment.user === 'object' && comment.user.id === currentUserId && (
          <CommentDeleteButton
            commentId={comment.id}
            onDelete={onCommentAdded}
          />
        )}
      </div>
      
      {/* Formulaire de réponse */}
      {showReplyForm && (
        <form onSubmit={handleSubmitReply} className="comment-form" style={{marginTop: '12px', marginLeft: '20px'}}>
          <textarea
            value={replyContent}
            onChange={(e) => setReplyContent(e.target.value)}
            placeholder="Écrivez votre réponse..."
            rows={2}
            disabled={submittingReply}
          />
          <div style={{display: 'flex', gap: '8px', marginTop: '8px'}}>
            <button 
              type="submit" 
              disabled={submittingReply || !replyContent.trim()}
            >
              {submittingReply ? '⏳ Envoi...' : '✨ Publier'}
            </button>
            <button 
              type="button" 
              className="glass-button"
              onClick={() => {
                setShowReplyForm(false);
                setReplyContent('');
              }}
              disabled={submittingReply}
            >
              ❌ Annuler
            </button>
          </div>
        </form>
      )}

      {/* Affichage des réponses */}
      {showReplies && (
        <div className="replies" style={{marginLeft: '20px', marginTop: '12px'}}>
          {loadingReplies ? (
            <div className="glass-loading">
              <div className="loading-spinner"></div>
              <p style={{marginLeft: '8px', color: 'var(--text-secondary)'}}>Chargement des réponses...</p>
            </div>
          ) : replies.length > 0 ? (
            replies.map((reply) => (
              <CommentItem 
                key={reply.id} 
                comment={reply} 
                onCommentAdded={onCommentAdded}
                publicationId={publicationId}
                currentUserId={currentUserId}
              />
            ))
          ) : (
            <div className="glass-card" style={{textAlign: 'center', padding: '16px'}}>
              <p style={{color: 'var(--text-secondary)', fontSize: '0.9rem'}}>
                💭 Aucune réponse pour le moment
              </p>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default CommentList; 
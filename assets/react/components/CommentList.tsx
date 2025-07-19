import React, { useState, useEffect } from 'react';
import { fetchWithAuth, getToken } from '../api/auth';

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

  const fetchComments = async () => {
    try {
      setLoading(true);
      console.log('DEBUG COMMENT LIST - Fetching comments for publicationId:', publicationId);
      
      const response = await fetchWithAuth(`/api/commentaires?publication=${publicationId}`);
      const data = await response.json();
      const allComments = data['hydra:member'] || data.member || [];
      
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
      <div className="comments">
        <p>Chargement des commentaires...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="comments">
        <p>Erreur: {error}</p>
      </div>
    );
  }

  return (
    <div className="comments">
      {/* Formulaire pour ajouter un nouveau commentaire */}
      <form onSubmit={handleSubmitComment} className="comment-form">
        <textarea
          value={newComment}
          onChange={(e) => setNewComment(e.target.value)}
          placeholder="Partagez votre pensée..."
          className="comment-input"
          rows={3}
          disabled={submitting}
        />
        <button 
          type="submit" 
          className="comment-submit-btn"
          disabled={submitting || !newComment.trim()}
        >
          {submitting ? 'Envoi...' : 'Publier le commentaire'}
        </button>
      </form>

      {/* Liste des commentaires */}
      <div className="comments-list">
        {comments.length === 0 ? (
          <p>Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
        ) : (
          comments.map((comment) => (
            <CommentItem 
              key={`${publicationId}-${comment.id}`} 
              comment={comment} 
              onCommentAdded={fetchComments}
              publicationId={publicationId}
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
}

const CommentItem: React.FC<CommentItemProps> = ({ comment, onCommentAdded, publicationId }) => {
  const [showReplies, setShowReplies] = useState(false);
  const [replies, setReplies] = useState<Comment[]>([]);
  const [showReplyForm, setShowReplyForm] = useState(false);
  const [replyContent, setReplyContent] = useState('');
  const [submittingReply, setSubmittingReply] = useState(false);
  const [loadingReplies, setLoadingReplies] = useState(false);

  const handleShowReplies = async () => {
    if (!showReplies) {
      // Si les réponses ne sont pas encore chargées
      if (replies.length === 0) {
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
            const response = await fetchWithAuth(`/api/commentaires?publication=${publicationId}&parent=${comment.id}`);
            const data = await response.json();
            const repliesData = data['hydra:member'] || data.member || [];
            console.log('DEBUG REPLIES - Réponses récupérées depuis API:', repliesData);
            console.log('DEBUG REPLIES - Structure d\'une réponse:', repliesData[0]);
            setReplies(repliesData);
          }
        } catch (err) {
          console.error('Erreur lors du chargement des réponses:', err);
        } finally {
          setLoadingReplies(false);
        }
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
        await onCommentAdded(); // Recharger tous les commentaires
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
    <div className="comment-item">
      <div className="comment-header">
        <div className="comment-avatar">
          {getInitials(typeof comment.user === 'object' ? comment.user.username : 'Utilisateur')}
        </div>
        <div className="comment-meta">
          <p className="comment-author">@{typeof comment.user === 'object' ? comment.user.username : 'utilisateur'}</p>
          <p className="comment-date">{formatDate(comment.createdAt)}</p>
        </div>
      </div>
      
      {/* Debug info pour les réponses - à supprimer plus tard */}
      {process.env.NODE_ENV === 'development' && comment.parent && (
        <div style={{fontSize: '10px', color: 'orange', marginTop: '5px'}}>
          DEBUG REPONSE: user={JSON.stringify(comment.user)}, createdAt={JSON.stringify(comment.createdAt)}
        </div>
      )}
      
      <div className="comment-content">
        {comment.content}
      </div>
      
      <div className="comment-actions">
        <button 
          className="comment-action-btn reply-btn"
          onClick={(e) => {
            e.preventDefault();
            setShowReplyForm(!showReplyForm);
          }}
        >
          💬
          Répondre
        </button>
        
        {comment.replies && comment.replies.length > 0 && (
          <button 
            className="comment-action-btn show-replies-btn"
            onClick={(e) => {
              e.preventDefault();
              handleShowReplies();
            }}
          >
            {showReplies ? '👆' : '👇'}
            {showReplies ? 'Masquer' : 'Afficher'} {comment.replies.length} réponse{comment.replies.length > 1 ? 's' : ''}
          </button>
        )}
      </div>
      
      {/* Formulaire de réponse */}
      {showReplyForm && (
        <form onSubmit={handleSubmitReply} className="reply-form">
          <textarea
            value={replyContent}
            onChange={(e) => setReplyContent(e.target.value)}
            placeholder="Écrivez votre réponse..."
            className="reply-input"
            rows={2}
            disabled={submittingReply}
          />
          <div className="reply-form-actions">
            <button 
              type="submit" 
              className="reply-submit-btn"
              disabled={submittingReply || !replyContent.trim()}
            >
              {submittingReply ? 'Envoi...' : 'Publier la réponse'}
            </button>
            <button 
              type="button" 
              className="reply-cancel-btn"
              onClick={() => {
                setShowReplyForm(false);
                setReplyContent('');
              }}
              disabled={submittingReply}
            >
              Annuler
            </button>
          </div>
        </form>
      )}

      {/* Affichage des réponses */}
      {showReplies && (
        <div className="replies">
          {loadingReplies ? (
            <p className="loading-message">Chargement des réponses...</p>
          ) : replies.length > 0 ? (
            replies.map((reply) => (
              <CommentItem 
                key={reply.id} 
                comment={reply} 
                onCommentAdded={onCommentAdded}
                publicationId={publicationId}
              />
            ))
          ) : (
            <p>Aucune réponse pour le moment</p>
          )}
        </div>
      )}
    </div>
  );
};

export default CommentList; 
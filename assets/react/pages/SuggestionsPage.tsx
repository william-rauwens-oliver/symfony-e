import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { getToken, fetchWithAuth } from '../api/auth';
import { useUser } from '../context/UserContext';
import { followUser, unfollowUser, isFollowingUser } from '../api/publication';
import LikeButton from '../components/LikeButton';
import CommentList from '../components/CommentList';
import RepostButton from '../components/RepostButton';
import ShareButton from '../components/ShareButton';

const SuggestionsPage: React.FC = () => {
  const { user, loading: userLoading } = useUser();
  const [suggestions, setSuggestions] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [apiMessage, setApiMessage] = useState<string | null>(null);
  const [followings, setFollowings] = useState<{[userId: number]: boolean}>({});

  useEffect(() => {
    // Attendre que le contexte utilisateur soit initialisé
    if (userLoading) {
      return;
    }

    // Si pas d'utilisateur connecté, ne pas faire l'appel API
    if (!user) {
      setError('Vous devez être connecté pour voir les suggestions.');
      setLoading(false);
      return;
    }

    setLoading(true);
    setError(null);
    
    fetchWithAuth('/api/suggestions')
      .then(async res => {
        if (res.status === 401) {
          setError('Session expirée ou non authentifié. Veuillez vous reconnecter.');
          setLoading(false);
          return null;
        }
        if (!res.ok) throw new Error('Erreur lors du chargement des suggestions');
        return res.json();
      })
      .then(data => {
        if (!data) return;
        if (data.error === 'Non authentifié') {
          setError('Session expirée ou non authentifié. Veuillez vous reconnecter.');
          setLoading(false);
          return;
        }
        setSuggestions(data.suggestions || []);
        setApiMessage(data.message || null);
        setLoading(false);
      })
      .catch(err => {
        setError(err.message);
        setLoading(false);
      });
  }, [user, userLoading]);

  // Vérifie le follow pour chaque auteur de publication
  useEffect(() => {
    if (!user || !suggestions.length) return;
    
    const token = getToken();
    if (!token) return;
    
    const uniqueAuthors = Array.from(new Set(suggestions.map(s => s.publication.author?.id).filter(Boolean)));
    
    uniqueAuthors.forEach(authorId => {
      if (authorId !== user.id) {
        isFollowingUser(authorId, token, user.id)
          .then(isFollow => {
            setFollowings(f => ({ ...f, [authorId]: isFollow }));
          })
          .catch(err => {
            console.error('Error checking follow for', authorId, ':', err);
            setFollowings(f => ({ ...f, [authorId]: false }));
          });
      }
    });
  }, [user, suggestions]);

  const handleFollow = async (authorId: number) => {
    if (!user) return;
    
    const token = getToken();
    if (!token) return;
    
    try {
      if (followings[authorId]) {
        await unfollowUser(authorId, token, user.id);
        setFollowings(f => ({ ...f, [authorId]: false }));
      } else {
        await followUser(authorId, token);
        setFollowings(f => ({ ...f, [authorId]: true }));
      }
      
      // Forcer une vérification de l'état après l'action
      const newFollowStatus = await isFollowingUser(authorId, token, user.id);
      setFollowings(f => ({ ...f, [authorId]: newFollowStatus }));
      
    } catch (error) {
      console.error('Error during follow/unfollow:', error);
    }
  };

  return (
    <>
      <div className="main-header">
        <h1>Suggestions personnalisées</h1>
      </div>
      {userLoading && <p>Vérification de l'authentification...</p>}
      {loading && !userLoading && <p>Chargement des suggestions...</p>}
      {error && <p style={{ color: 'red' }}>{error}</p>}
      {apiMessage && (
        <div className="publication">
          <div className="publication-content">
            <p style={{ color: 'orange' }}>{apiMessage}</p>
          </div>
        </div>
      )}
      {!loading && !error && suggestions.length === 0 && (
        <div className="publication">
          <div className="publication-content">
            <p>Aucune suggestion pour le moment.</p>
          </div>
        </div>
      )}
      {!loading && !error && suggestions.map((s, i) => {
        const pub = s.publication; // L'API retourne { publication: {...}, score: ..., scoreDetails: ... }
        const score = s.score;
        const scoreDetails = s.scoreDetails || {};
        return (
          <article className="publication" key={pub.id}>
            <div className="publication-header">
              <span className="publication-author">{pub.author?.username || 'Utilisateur inconnu'}</span>
              <span className="publication-username">@{pub.author?.username || 'user'}</span>
              {/* Bouton Follow/Unfollow */}
              {user && pub.author?.id !== user.id && pub.author?.id && (
                <button
                  className="follow-btn"
                  style={{marginLeft:8, padding:'2px 10px', borderRadius:8, border:'1px solid #1da1f2', background: followings[pub.author.id] ? '#e3f2fd' : '#1da1f2', color: followings[pub.author.id] ? '#1da1f2' : '#fff', fontWeight:'bold', cursor:'pointer'}}
                  onClick={() => handleFollow(pub.author.id)}
                >
                  {followings[pub.author.id] ? 'Ne plus suivre' : 'Suivre'}
                </button>
              )}
              <span className="publication-date">· {pub.createdAt ? new Date(pub.createdAt).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) : 'Date inconnue'}</span>
              <span className="suggestion-score">Score: {score}</span>
            </div>
            <div className="publication-content">
              {(pub.content || 'AUCUN CONTENU').split(/(#[\w\d_]+)/g).map((part: string, idx: number) =>
                /^#[\w\d_]+$/.test(part) ? (
                  <Link key={idx} to={`/hashtag/${part.substring(1).toLowerCase()}`} className="hashtag">{part}</Link>
                ) : (
                  part
                )
              )}
              {pub.image && (
                <div className="publication-media">
                  <img src={`/uploads/images/${pub.image}`} alt="Image" style={{ maxWidth: '100%', borderRadius: 12, marginTop: 10 }} />
                </div>
              )}
              {pub.video && (
                <div className="publication-media">
                  <video controls style={{ maxWidth: '100%', borderRadius: 12, marginTop: 10 }}>
                    <source src={`/uploads/videos/${pub.video}`} type="video/mp4" />
                    Votre navigateur ne supporte pas la vidéo.
                  </video>
                </div>
              )}
            </div>
            <div className="score-details">
              <small>
                {scoreDetails.likes !== undefined && <>Likes: +{scoreDetails.likes} </>}
                {scoreDetails.comments !== undefined && <>| Commentaires: +{scoreDetails.comments} </>}
                {scoreDetails.following_likes !== undefined && <>| Suivis: +{scoreDetails.following_likes} </>}
                {scoreDetails.hashtag_matches !== undefined && <>| Hashtags: +{scoreDetails.hashtag_matches} </>}
                {scoreDetails.recent_interaction !== undefined && <>| Interaction récente: +{scoreDetails.recent_interaction} </>}
              </small>
            </div>
            <div className="publication-actions">
              <LikeButton publicationId={pub.id} initialCount={pub.likes || 0} />
              <RepostButton publicationId={pub.id} />
            </div>
            <div className="publication-comments">
              <CommentList publicationId={pub.id} />
            </div>
          </article>
        );
      })}
    </>
  );
};

export default SuggestionsPage; 
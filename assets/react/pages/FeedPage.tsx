import React, { useEffect, useState } from 'react';
import { useUser } from '../context/UserContext';
import PublicationForm from '../components/PublicationForm';
import PublicationList from '../components/PublicationList';
import PublicationDeleteButton from '../components/PublicationDeleteButton';
import './Feed.css';
import { fetchWithAuth } from '../api/auth';
import { fetchRepostsByUser } from '../api/publication';
import RepostButton from '../components/RepostButton';
import { fetchPublications } from '../api/publication';
import { followUser, unfollowUser, isFollowingUser } from '../api/publication';
import { getToken } from '../api/auth';
import LikeButton from '../components/LikeButton';
import CommentList from '../components/CommentList';

const FeedPage: React.FC = () => {
  const { user } = useUser();
  const [refresh, setRefresh] = useState(false);
  const [suggestions, setSuggestions] = useState<any[]>([]);
  const [loadingSuggestions, setLoadingSuggestions] = useState(true);
  const [reposts, setReposts] = useState<any[]>([]);
  const [publications, setPublications] = useState<any[]>([]);
  const [followings, setFollowings] = useState<{[userId: number]: boolean}>({});


  console.log('DEBUG FEEDPAGE - Component mounted');

  useEffect(() => {
    setLoadingSuggestions(true);
    fetchWithAuth('/api/suggestions')
      .then(res => res.json())
      .then(data => {
        setSuggestions(data.suggestions || []);
        setLoadingSuggestions(false);
      })
      .catch(() => setLoadingSuggestions(false));
  }, [refresh]);

  useEffect(() => {
    if (user) {
      fetchRepostsByUser(user.id).then(setReposts);
    }
  }, [user]);

  useEffect(() => {
    // Récupérer les publications simples à chaque refresh ou au montage
    const token = getToken();
    fetchPublications(token || undefined).then(setPublications);
  }, [refresh]);

  // Vérifie le follow pour chaque auteur de publication
  useEffect(() => {
    console.log('DEBUG FEED FOLLOW CHECK - useEffect triggered');
    console.log('DEBUG FEED FOLLOW CHECK - user:', user);
    console.log('DEBUG FEED FOLLOW CHECK - publications count:', publications.length);
    
    if (!user || !publications.length) {
      console.log('DEBUG FEED FOLLOW CHECK - Missing user or publications, returning');
      return;
    }
    
    const token = getToken();
    if (!token) {
      console.log('DEBUG FEED FOLLOW CHECK - No token, returning');
      return;
    }
    
    const uniqueAuthors = Array.from(new Set(publications.map(pub => pub.user?.id).filter(Boolean)));
    console.log('DEBUG FEED FOLLOW CHECK - Unique authors:', uniqueAuthors);
    
    uniqueAuthors.forEach(authorId => {
      if (authorId !== user.id) {
        console.log('DEBUG FEED FOLLOW CHECK - Checking follow status for authorId:', authorId);
        isFollowingUser(authorId, token, user.id)
          .then(isFollow => {
            console.log('DEBUG FEED FOLLOW CHECK - Follow status for', authorId, ':', isFollow);
            setFollowings(f => ({ ...f, [authorId]: isFollow }));
          })
          .catch(err => {
            console.error('DEBUG FEED FOLLOW CHECK - Error checking follow for', authorId, ':', err);
            setFollowings(f => ({ ...f, [authorId]: false }));
          });
      }
    });
  }, [user, publications]);



  const handleFollow = async (authorId: number) => {
    console.log('DEBUG FEED FOLLOW - Function called for authorId:', authorId);
    console.log('DEBUG FEED FOLLOW - Current followings state:', followings);
    
    if (!user) {
      console.log('DEBUG FEED FOLLOW - No user, returning');
      return;
    }
    
    const token = getToken();
    if (!token) {
      console.log('DEBUG FEED FOLLOW - No token, returning');
      return;
    }
    
    try {
      if (followings[authorId]) {
        console.log('DEBUG FEED FOLLOW - Unfollowing user:', authorId);
        await unfollowUser(authorId, token, user.id);
        console.log('DEBUG FEED FOLLOW - Unfollow successful, setting to false');
        setFollowings(f => ({ ...f, [authorId]: false }));
      } else {
        console.log('DEBUG FEED FOLLOW - Following user:', authorId);
        await followUser(authorId, token);
        console.log('DEBUG FEED FOLLOW - Follow successful, setting to true');
        setFollowings(f => ({ ...f, [authorId]: true }));
      }
      
      // Forcer une vérification de l'état après l'action
      console.log('DEBUG FEED FOLLOW - Rechecking follow status after action');
      const newFollowStatus = await isFollowingUser(authorId, token, user.id);
      console.log('DEBUG FEED FOLLOW - New follow status from API:', newFollowStatus);
      setFollowings(f => ({ ...f, [authorId]: newFollowStatus }));
      
    } catch (error) {
      console.error('DEBUG FEED FOLLOW - Error during follow/unfollow:', error);
    }
  };

  const handleDeletePublication = () => {
    setRefresh(r => !r); // Rafraîchir les publications
  };

  console.log('DEBUG FEED - publications', publications);
  console.log('DEBUG FEED - reposts', reposts);

  return (
    <div className="feed-container">
      <div className="glass-header">
        <h1>🏠 Accueil</h1>
        <p>
          {user ? `Bienvenue ${user.username} !` : 'Bienvenue sur le réseau'}
        </p>
      </div>
      {user && <PublicationForm onPublish={() => setRefresh(r => !r)} />}
      {/* Affichage des publications et des reposts */}
      {[...publications, ...reposts.map(r => ({...r.publication, reposted: true, repostedBy: r.user, repostedAt: r.createdAt}))]
        .sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
        .map(pub => (
          <article className={`glass-publication${pub.reposted ? ' reposted' : ''}`} key={pub.id + (pub.reposted ? '-repost' : '')} style={pub.reposted ? {borderLeft:'4px solid #34C759',background:'rgba(52, 199, 89, 0.1)'} : {}}>
            <div className="publication-header">
              {pub.reposted && (
                <span className="repost-label" style={{color:'#388e3c',fontWeight:'bold',marginRight:8}}>Reposté</span>
              )}
              <span className="publication-author">{pub.user?.username || 'Utilisateur inconnu'}</span>
              <span className="publication-username">@{pub.user?.username || 'user'}</span>
              {/* Bouton Follow/Unfollow */}
              {user && pub.user?.id !== user.id && pub.user?.id && (
                <button
                  className="follow-btn"
                  style={{marginLeft:8, padding:'2px 10px', borderRadius:8, border:'1px solid #1da1f2', background: followings[pub.user.id] ? '#e3f2fd' : '#1da1f2', color: followings[pub.user.id] ? '#1da1f2' : '#fff', fontWeight:'bold', cursor:'pointer'}}
                  onClick={() => handleFollow(pub.user.id)}
                >
                  {followings[pub.user.id] ? 'Ne plus suivre' : 'Suivre'}
                </button>
              )}
              <span className="publication-date">· {pub.createdAt ? new Date(pub.createdAt).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) : 'Date inconnue'}</span>
              {pub.reposted && pub.repostedAt && (
                <span style={{marginLeft:8, color:'#888'}}>({new Date(pub.repostedAt).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })})</span>
              )}
            </div>
            <div className="publication-content">
              {pub.texte || pub.content || 'AUCUN CONTENU'}
              {pub.image && (
                <div className="publication-media">
                  <img 
                    src={pub.image.startsWith('http') ? pub.image : pub.image} 
                    alt="Image" 
                    style={{ maxWidth: '100%', borderRadius: '12px', marginTop: '10px' }}
                    onLoad={() => console.log('✅ Image chargée dans FeedPage:', pub.image)}
                    onError={(e) => {
                      console.error('❌ Erreur image dans FeedPage:', pub.image, e);
                      e.currentTarget.style.display = 'none';
                    }}
                  />
                </div>
              )}
              {pub.video && (
                <div className="publication-media">
                  <video controls style={{ maxWidth: '100%', borderRadius: '12px', marginTop: '10px' }}>
                    <source src={pub.video.startsWith('http') ? pub.video : `/uploads/videos/${pub.video}`} type="video/mp4" />
                    Votre navigateur ne supporte pas la vidéo.
                  </video>
                </div>
              )}
            </div>
            <div className="publication-actions">
              <LikeButton publicationId={pub.id} initialCount={pub.likeCount || 0} initiallyLiked={pub.likedByCurrentUser || false} />
              <RepostButton publicationId={pub.id} />
              {/* Bouton de suppression pour nos propres publications */}
              {user && pub.user?.id === user.id && (
                <PublicationDeleteButton
                  publicationId={pub.id}
                  onDelete={handleDeletePublication}
                />
              )}
            </div>
            <div className="publication-comments">
              <CommentList publicationId={pub.id} />
            </div>
          </article>
        ))}
      

    </div>
  );
};

export default FeedPage; 
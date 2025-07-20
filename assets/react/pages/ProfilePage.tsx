import React, { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useUser } from '../context/UserContext';
import './Profile.css';
import { getToken, fetchWithAuth } from '../api/auth';
import { followUser, unfollowUser, isFollowingUser, fetchRepostsByUser } from '../api/publication';
import RepostButton from '../components/RepostButton';
import LikeButton from '../components/LikeButton';
import CommentForm from '../components/CommentForm';
import CommentList from '../components/CommentList';
import PublicationDeleteButton from '../components/PublicationDeleteButton';

const ProfilePage: React.FC = () => {
  console.log('DEBUG PROFILE PAGE - Component mounted (top of function)');
  
  const { id } = useParams<{ id?: string }>();
  const { user } = useUser();
  const [profile, setProfile] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const navigate = useNavigate();
  const [publicationCount, setPublicationCount] = useState<number>(0);
  const [publications, setPublications] = useState<any[]>([]);
  const [likedPublicationsCount, setLikedPublicationsCount] = useState<number>(0);
  const [likedCommentsCount, setLikedCommentsCount] = useState<number>(0);
  const [isFollowing, setIsFollowing] = useState<boolean>(false);
  const [followLoading, setFollowLoading] = useState(false);
  const [reposts, setReposts] = useState<any[]>([]);
  const [followersCount, setFollowersCount] = useState<number>(0);
  const [followingsCount, setFollowingsCount] = useState<number>(0);


  console.log('DEBUG PROFILE PAGE - Composant monté, id:', id);
  console.log('DEBUG PROFILE PAGE - id from params:', id);
  console.log('DEBUG PROFILE PAGE - current user:', user);

  useEffect(() => {
    console.log('DEBUG PROFILE PAGE - useEffect profil, id:', id);
    setLoading(true);
    setError(null);
    
    const url = `/api/users/${id}`;
    console.log('DEBUG PROFILE PAGE - Fetching from:', url);
    
    fetch(url)
      .then(res => {
        console.log('DEBUG PROFILE PAGE - Response status:', res.status);
        if (!res.ok) throw new Error('Erreur lors du chargement du profil');
        return res.json();
      })
      .then(data => {
        console.log('DEBUG PROFILE PAGE - Data received:', data);
        setProfile(data);
        setLoading(false);
      })
      .catch(err => {
        console.log('DEBUG PROFILE PAGE - Error:', err.message);
        setError(err.message);
        setLoading(false);
      });
  }, [id]);

  useEffect(() => {
    if (!id) return;
    
    // Récupérer les publications
    fetch(`/api/publications?user=${id}`)
      .then(res => res.json())
      .then(data => {
        const pubs = data['hydra:member'] || data.member || [];
        setPublications(pubs);
      });
      
    // Récupérer les reposts
    fetch(`/api/reposts?user=${id}`)
      .then(res => res.json())
      .then(data => {
        const repostsData = data['hydra:member'] || data.member || [];
        setReposts(repostsData);
      })
      .catch(err => {
        console.error('Error fetching reposts:', err);
      });
      
    // Essayer aussi avec la fonction importée
    try {
      fetchRepostsByUser(Number(id))
        .then(data => {
          setReposts(data);
        })
        .catch(err => {
          console.error('Error with function call:', err);
        });
    } catch (err) {
      console.error('Error calling fetchRepostsByUser:', err);
    }
  }, [id]);

  useEffect(() => {
    if (!id) return;
    // Récupérer les publications likées par l'utilisateur avec JWT
    fetchWithAuth(`/api/likes?user=${id}`)
      .then(res => res.json())
      .then(data => {
        const likesArr = data['hydra:member'] || data.member || data;
        // On compte les publications distinctes
        const pubs = likesArr.map((like: any) => like.publication);
        setLikedPublicationsCount(new Set(pubs).size);
      });
  }, [id]);

  useEffect(() => {
    if (!id) return;
    fetchWithAuth(`/api/comment_likes?user=${id}`)
      .then(res => res.json())
      .then(data => {
        const commentLikesArr = data['hydra:member'] || data.member || data;
        // On compte les commentaires distincts likés
        const comments = commentLikesArr.map((like: any) => like.commentaire);
        setLikedCommentsCount(new Set(comments).size);
      });
  }, [id]);

  useEffect(() => {
    if (!user || !profile) {
      return;
    }
    
    if (user.id === profile.id) {
      setIsFollowing(false);
      return;
    }
    
    const token = getToken();
    if (!token) {
      return;
    }
    
    isFollowingUser(profile.id, token, user.id)
      .then(result => {
        setIsFollowing(result);
      })
      .catch(err => {
        console.error('Error checking follow status:', err);
        setIsFollowing(false);
      });
  }, [user, profile]);

  useEffect(() => {
    if (!id) return;
    
    // Followers (ceux qui me suivent)
    fetch(`/api/follows?followed=${id}`)
      .then(res => res.json())
      .then(data => {
        const arr = data['hydra:member'] || data.member || data;
        setFollowersCount(arr.length);
      })
      .catch(err => {
        console.error('Error fetching followers:', err);
      });
      
    // Followings (ceux que je suis)
    fetch(`/api/follows?follower=${id}`)
      .then(res => res.json())
      .then(data => {
        const arr = data['hydra:member'] || data.member || data;
        setFollowingsCount(arr.length);
      })
      .catch(err => {
        console.error('Error fetching followings:', err);
      });
  }, [id]);

  const handleFollow = async () => {
    if (!user || !profile) {
      return;
    }
    
    setFollowLoading(true);
    const token = getToken();
    if (!token) {
      setFollowLoading(false);
      return;
    }
    
    try {
      if (isFollowing) {
        await unfollowUser(profile.id, token, user.id);
        setIsFollowing(false);
      } else {
        await followUser(profile.id, token);
        setIsFollowing(true);
      }
      
      // Forcer une vérification de l'état après l'action
      const newFollowStatus = await isFollowingUser(profile.id, token, user.id);
      setIsFollowing(newFollowStatus);
      
    } catch (error) {
      console.error('Error during follow/unfollow:', error);
    } finally {
      setFollowLoading(false);
    }
  };

  const handleDeletePublication = () => {
    // Rafraîchir les données du profil
    window.location.reload();
  };



  const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
  };

  if (loading) {
    console.log('DEBUG PROFILE PAGE - Rendering loading state');
    return (
      <div className="profile-container">
        <div className="glass-loading">
          <div className="loading-spinner"></div>
          <p style={{marginLeft: '12px', color: 'var(--text-secondary)'}}>Chargement du profil...</p>
        </div>
      </div>
    );
  }
  
  if (error || !profile) {
    console.log('DEBUG PROFILE PAGE - Rendering error state');
    return (
      <div className="profile-container">
        <div className="glass-error">
          ❌ {error || 'Profil introuvable'}
        </div>
      </div>
    );
  }

  console.log('DEBUG PROFILE PAGE - Rendering profile data');

  const totalLikes = likedPublicationsCount + likedCommentsCount;

  return (
    <div className="profile-container">
      <div className="glass-profile">
        <div className="profile-avatar">
          {getInitials(profile.username)}
        </div>
        
        <div className="profile-details">
          <h2 className="profile-name">{profile.username}</h2>
          <p className="profile-username">@{profile.username}</p>
          <p className="profile-email">{profile.email}</p>
          
          <div className="profile-follow-stats">
            <div className="profile-follow-stat">
              <span className="profile-follow-number">{followingsCount}</span>
              <span className="profile-follow-label">Abonnement{followingsCount === 1 ? '' : 's'}</span>
            </div>
            <div className="profile-follow-stat">
              <span className="profile-follow-number">{followersCount}</span>
              <span className="profile-follow-label">Abonné{followersCount === 1 ? '' : 's'}</span>
            </div>
          </div>

          {/* Bouton Follow/Unfollow si ce n'est pas mon profil */}
          {user && user.id !== profile.id && (
            <button className="glass-button primary" onClick={handleFollow} disabled={followLoading} style={{margin:'1em 0'}}>
              {isFollowing ? '🚫 Ne plus suivre' : '✨ Suivre'}
            </button>
          )}
          
          {/* Actions pour mon propre profil */}
          {user && user.id === profile.id && (
            <div className="profile-actions">
              <Link to="/profile/edit" className="glass-button">
                ✏️ Modifier
              </Link>
              <Link to="/profile/delete" className="glass-button" style={{background: 'rgba(255, 59, 48, 0.1)', borderColor: 'rgba(255, 59, 48, 0.2)', color: '#FF3B30'}}>
                🗑️ Supprimer
              </Link>
            </div>
          )}
        </div>
      </div>

      {/* Statistiques détaillées */}
      <div className="glass-card" style={{marginTop: '24px'}}>
        <h3 style={{color: 'var(--text-primary)', marginBottom: '16px', fontSize: '1.2rem'}}>📊 Statistiques</h3>
        <div className="profile-stats">
          <div className="profile-stat">
            <span className="profile-stat-number">{profile.publicationCount ?? '...'}</span>
            <span className="profile-stat-label">Publications</span>
          </div>
          <div className="profile-stat">
            <span className="profile-stat-number">👥 {followingsCount}</span>
            <span className="profile-stat-label">Abonnements</span>
          </div>
          <div className="profile-stat">
            <span className="profile-stat-number">👤 {followersCount}</span>
            <span className="profile-stat-label">Abonnés</span>
          </div>
          <div className="profile-stat">
            <span className="profile-stat-number">❤️ {totalLikes}</span>
            <span className="profile-stat-label">Likes totaux</span>
          </div>
          <div className="profile-stat">
            <span className="profile-stat-number">📝 {likedPublicationsCount}</span>
            <span className="profile-stat-label">Tweets likés</span>
          </div>
          <div className="profile-stat">
            <span className="profile-stat-number">💬 {likedCommentsCount}</span>
            <span className="profile-stat-label">Commentaires likés</span>
          </div>
          <div className="profile-stat">
            <span className="profile-stat-number">💭 {profile.commentaires?.length || 0}</span>
            <span className="profile-stat-label">Commentaires</span>
          </div>
        </div>
      </div>


      {/* Liste des publications et retweets de l'utilisateur */}
      <div className="profile-publications" style={{marginTop: '24px'}}>
        <h3 style={{color: 'var(--text-primary)', marginBottom: '16px', fontSize: '1.2rem'}}>📝 Publications et retweets</h3>
        {(() => {
          // Combiner les publications et reposts
          const allContent: Array<{
            type: 'publication' | 'repost';
            content: any;
            date: Date;
          }> = [];
          
          // Ajouter les publications
          publications.forEach(pub => {
            allContent.push({
              type: 'publication',
              content: pub,
              date: new Date(pub.createdAt)
            });
          });
          
          // Ajouter les reposts
          reposts.forEach(repost => {
            allContent.push({
              type: 'repost',
              content: repost,
              date: new Date(repost.createdAt)
            });
          });
          
          // Trier par date décroissante
          allContent.sort((a, b) => b.date.getTime() - a.date.getTime());
          
          if (allContent.length === 0) {
            return (
              <div className="glass-card" style={{textAlign: 'center', padding: '32px'}}>
                <p style={{color: 'var(--text-secondary)', fontSize: '1rem'}}>
                  💭 Aucune publication ou retweet pour le moment.
                </p>
              </div>
            );
          }
          
          return allContent.map((item, index) => {
            if (item.type === 'publication') {
              const pub = item.content;
              return (
                <article className="glass-publication" key={`pub-${pub.id}`}>
                  <div className="publication-header">
                    <span className="publication-author">{pub.user?.username || 'Utilisateur inconnu'}</span>
                    <span className="publication-username">@{pub.user?.username || 'user'}</span>
                    <span className="publication-date">· {pub.createdAt ? new Date(pub.createdAt).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) : 'Date inconnue'}</span>
                  </div>
                  <div className="publication-content">
                    {pub.texte || pub.content || 'AUCUN CONTENU'}
                    {pub.image && (
                      <div className="publication-media">
                        <img 
                          src={pub.image.startsWith('http') ? pub.image : pub.image} 
                          alt="Image" 
                          style={{ maxWidth: '100%', borderRadius: '12px', marginTop: '10px' }}
                          onLoad={() => console.log('✅ Image chargée dans ProfilePage:', pub.image)}
                          onError={(e) => {
                            console.error('❌ Erreur image dans ProfilePage:', pub.image, e);
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
              );
            } else {
              const repost = item.content;
              return (
                <article className="glass-publication reposted" key={`repost-${repost.id}`} style={{borderLeft:'4px solid #34C759',background:'rgba(52, 199, 89, 0.1)'}}>
                  <div className="publication-header">
                    <div className="repost-indicator" style={{display:'flex',alignItems:'center',gap:'4px',color:'#388e3c',fontSize:'0.9em',marginBottom:'8px'}}>
                      <span style={{fontSize:'1.2em'}}>🔄</span>
                      <span style={{fontWeight:'bold'}}>{profile?.username} a retweeté</span>
                    </div>
                    <span className="publication-author">{repost.publication?.user?.username || 'Utilisateur inconnu'}</span>
                    <span className="publication-username">@{repost.publication?.user?.username || 'user'}</span>
                    <span className="publication-date">· {repost.publication?.createdAt ? new Date(repost.publication.createdAt).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) : 'Date inconnue'}</span>
                  </div>
                  <div className="publication-content">
                    {repost.publication?.texte || repost.publication?.content || 'AUCUN CONTENU'}
                    {repost.publication?.image && (
                      <div className="publication-media">
                        <img 
                          src={repost.publication.image.startsWith('http') ? repost.publication.image : repost.publication.image} 
                          alt="Image" 
                          style={{ maxWidth: '100%', borderRadius: '12px', marginTop: '10px' }}
                          onLoad={() => console.log('✅ Image chargée dans ProfilePage (repost):', repost.publication.image)}
                          onError={(e) => {
                            console.error('❌ Erreur image dans ProfilePage (repost):', repost.publication.image, e);
                            e.currentTarget.style.display = 'none';
                          }}
                        />
                      </div>
                    )}
                    {repost.publication?.video && (
                      <div className="publication-media">
                        <video controls style={{ maxWidth: '100%', borderRadius: '12px', marginTop: '10px' }}>
                          <source src={repost.publication.video.startsWith('http') ? repost.publication.video : `/uploads/videos/${repost.publication.video}`} type="video/mp4" />
                          Votre navigateur ne supporte pas la vidéo.
                        </video>
                      </div>
                    )}
                  </div>
                  <div className="publication-actions">
                    <RepostButton publicationId={repost.publication?.id} />
                  </div>
                </article>
              );
            }
          });
        })()}
      </div>
      

    </div>
  );
};

export default ProfilePage; 
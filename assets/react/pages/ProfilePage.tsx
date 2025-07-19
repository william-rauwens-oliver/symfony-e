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
    console.log('DEBUG REPOSTS - useEffect triggered, id:', id);
    if (!id) return;
    
    // Récupérer les publications
    fetch(`/api/publications?user=${id}`)
      .then(res => res.json())
      .then(data => {
        const pubs = data['hydra:member'] || data.member || [];
        console.log('DEBUG PUBLICATIONS - Received:', pubs.length, 'publications');
        setPublications(pubs);
      });
      
    // Récupérer les reposts
    console.log('DEBUG REPOSTS - Fetching reposts for user', id);
    console.log('DEBUG REPOSTS - fetchRepostsByUser function:', typeof fetchRepostsByUser);
    
    // Test direct de l'API
    fetch(`/api/reposts?user=${id}`)
      .then(res => res.json())
      .then(data => {
        console.log('DEBUG REPOSTS - Direct API call result:', data);
        const repostsData = data['hydra:member'] || data.member || [];
        console.log('DEBUG REPOSTS - Direct API call reposts count:', repostsData.length);
        setReposts(repostsData);
      })
      .catch(err => {
        console.error('DEBUG REPOSTS - Error with direct API call:', err);
      });
      
    // Essayer aussi avec la fonction importée
    try {
      fetchRepostsByUser(Number(id))
        .then(data => {
          console.log('DEBUG REPOSTS - Function call data received:', data);
          setReposts(data);
        })
        .catch(err => {
          console.error('DEBUG REPOSTS - Error with function call:', err);
        });
    } catch (err) {
      console.error('DEBUG REPOSTS - Error calling fetchRepostsByUser:', err);
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
    console.log('DEBUG FOLLOW CHECK - useEffect triggered');
    console.log('DEBUG FOLLOW CHECK - user:', user);
    console.log('DEBUG FOLLOW CHECK - profile:', profile);
    console.log('DEBUG FOLLOW CHECK - user.id === profile.id:', user?.id === profile?.id);
    
    if (!user || !profile) {
      console.log('DEBUG FOLLOW CHECK - Missing user or profile, returning');
      return;
    }
    
    if (user.id === profile.id) {
      console.log('DEBUG FOLLOW CHECK - Same user, setting isFollowing to false');
      setIsFollowing(false);
      return;
    }
    
    const token = getToken();
    if (!token) {
      console.log('DEBUG FOLLOW CHECK - No token, returning');
      return;
    }
    
    console.log('DEBUG FOLLOW CHECK - Calling isFollowingUser with profile.id:', profile.id);
    isFollowingUser(profile.id, token, user.id)
      .then(result => {
        console.log('DEBUG FOLLOW CHECK - isFollowingUser result:', result);
        setIsFollowing(result);
      })
      .catch(err => {
        console.error('DEBUG FOLLOW CHECK - Error checking follow status:', err);
        setIsFollowing(false);
      });
  }, [user, profile]);

  useEffect(() => {
    if (!id) return;
    console.log('DEBUG FOLLOWS - Fetching follows for user:', id);
    
    // Followers (ceux qui me suivent)
    fetch(`/api/follows?followed=${id}`)
      .then(res => {
        console.log('DEBUG FOLLOWS - Followers response status:', res.status);
        return res.json();
      })
      .then(data => {
        console.log('DEBUG FOLLOWS - Followers data:', data);
        const arr = data['hydra:member'] || data.member || data;
        console.log('DEBUG FOLLOWS - Followers count:', arr.length);
        setFollowersCount(arr.length);
      })
      .catch(err => {
        console.error('DEBUG FOLLOWS - Error fetching followers:', err);
      });
      
    // Followings (ceux que je suis)
    fetch(`/api/follows?follower=${id}`)
      .then(res => {
        console.log('DEBUG FOLLOWS - Followings response status:', res.status);
        return res.json();
      })
      .then(data => {
        console.log('DEBUG FOLLOWS - Followings data:', data);
        const arr = data['hydra:member'] || data.member || data;
        console.log('DEBUG FOLLOWS - Followings count:', arr.length);
        setFollowingsCount(arr.length);
      })
      .catch(err => {
        console.error('DEBUG FOLLOWS - Error fetching followings:', err);
      });
  }, [id]);

  const handleFollow = async () => {
    console.log('DEBUG HANDLE FOLLOW - Function called');
    console.log('DEBUG HANDLE FOLLOW - user:', user);
    console.log('DEBUG HANDLE FOLLOW - profile:', profile);
    console.log('DEBUG HANDLE FOLLOW - current isFollowing:', isFollowing);
    
    if (!user || !profile) {
      console.log('DEBUG HANDLE FOLLOW - Missing user or profile, returning');
      return;
    }
    
    setFollowLoading(true);
    const token = getToken();
    if (!token) {
      console.log('DEBUG HANDLE FOLLOW - No token, returning');
      setFollowLoading(false);
      return;
    }
    
    try {
      if (isFollowing) {
        console.log('DEBUG HANDLE FOLLOW - Unfollowing user:', profile.id);
        await unfollowUser(profile.id, token, user.id);
        console.log('DEBUG HANDLE FOLLOW - Unfollow successful, setting isFollowing to false');
        setIsFollowing(false);
      } else {
        console.log('DEBUG HANDLE FOLLOW - Following user:', profile.id);
        await followUser(profile.id, token);
        console.log('DEBUG HANDLE FOLLOW - Follow successful, setting isFollowing to true');
        setIsFollowing(true);
      }
      
      // Forcer une vérification de l'état après l'action
      console.log('DEBUG HANDLE FOLLOW - Rechecking follow status after action');
      const newFollowStatus = await isFollowingUser(profile.id, token, user.id);
      console.log('DEBUG HANDLE FOLLOW - New follow status from API:', newFollowStatus);
      setIsFollowing(newFollowStatus);
      
    } catch (error) {
      console.error('DEBUG HANDLE FOLLOW - Error during follow/unfollow:', error);
    } finally {
      setFollowLoading(false);
    }
  };

  console.log('DEBUG PROFILE PAGE - Juste avant return, id:', id);
  console.log('DEBUG PROFILE PAGE - Render state - loading:', loading, 'error:', error, 'profile:', profile);
  console.log('DEBUG PUBLICATION COUNT:', publicationCount);
  console.log('DEBUG REPOSTS', reposts);

  const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
  };

  if (loading) {
    console.log('DEBUG PROFILE PAGE - Rendering loading state');
    return (
      <div className="profile-container">
        <div className="profile-header">
          <div className="empty-profile">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="2" strokeOpacity="0.3"/>
              <path d="M12 2C6.47715 2 2 6.47715 2 12" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
            </svg>
            <h4>Chargement...</h4>
            <p>Récupération des informations du profil</p>
          </div>
        </div>
      </div>
    );
  }
  
  if (error || !profile) {
    console.log('DEBUG PROFILE PAGE - Rendering error state');
    return (
      <div className="profile-container">
        <div className="profile-header">
          <div className="empty-profile">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="2"/>
              <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" strokeWidth="2"/>
              <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" strokeWidth="2"/>
            </svg>
            <h4>Erreur</h4>
            <p>{error || 'Profil introuvable'}</p>
          </div>
        </div>
      </div>
    );
  }

  console.log('DEBUG PROFILE PAGE - Rendering profile data');

  const totalLikes = likedPublicationsCount + likedCommentsCount;

  return (
    <div className="profile-container">
      <div className="profile-header">
        <div className="profile-banner"></div>
        
        {user && user.id === profile.id && (
          <div className="profile-actions">
            <Link to="/profile/edit" className="profile-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M18.5 2.5C18.8978 2.10217 19.4374 1.87868 20 1.87868C20.5626 1.87868 21.1022 2.10217 21.5 2.5C21.8978 2.89782 22.1213 3.43739 22.1213 4C22.1213 4.56261 21.8978 5.10217 21.5 5.5L12 15L8 16L9 12L18.5 2.5Z" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              Modifier
            </Link>
            <Link to="/profile/delete" className="profile-btn danger">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <polyline points="3,6 5,6 21,6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M19,6V20C19,20.5304 18.7893,21.0391 18.4142,21.4142C18.0391,21.7893 17.5304,22 17,22H7C6.46957,22 5.96086,21.7893 5.58579,21.4142C5.21071,21.0391 5,20.5304 5,20V6M8,6V4C8,3.46957 8.21071,2.96086 8.58579,2.58579C8.96086,2.21071 9.46957,2 10,2H14C14.5304,2 15.0391,2.21071 15.4142,2.58579C15.7893,2.96086 16,3.46957 16,4V6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
              Supprimer
            </Link>
          </div>
        )}
        
        <div className="profile-info">
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
              <button className="profile-btn" onClick={handleFollow} disabled={followLoading} style={{margin:'1em 0'}}>
                {isFollowing ? 'Ne plus suivre' : 'Suivre'}
              </button>
            )}
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
                <span className="profile-stat-number">{totalLikes}</span>
                <span className="profile-stat-label">Likes totaux</span>
              </div>
              <div className="profile-stat">
                <span className="profile-stat-number">{likedPublicationsCount}</span>
                <span className="profile-stat-label">Tweets likés</span>
              </div>
              <div className="profile-stat">
                <span className="profile-stat-number">{likedCommentsCount}</span>
                <span className="profile-stat-label">Commentaires likés</span>
              </div>
              <div className="profile-stat">
                <span className="profile-stat-number">{profile.commentaires?.length || 0}</span>
                <span className="profile-stat-label">Commentaires</span>
              </div>
            </div>
          </div>
        </div>
      </div> {/* .profile-header */}

      {/* Debug JSON des publications et reposts */}
      {process.env.NODE_ENV !== 'production' && (
        <div style={{background:'#f5f5f5',border:'1px solid #ccc',padding:'1em',margin:'1em 0',fontSize:'0.9em',overflowX:'auto'}}>
          <strong>DEBUG Publications :</strong>
          <pre>{JSON.stringify(publications, null, 2)}</pre>
          <strong>DEBUG Reposts :</strong>
          <pre>{JSON.stringify(reposts, null, 2)}</pre>
        </div>
      )}
      {/* Liste des publications et retweets de l'utilisateur */}
      <div className="profile-publications">
        <h3>Publications et retweets</h3>
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
              <div className="publication">
                <div className="publication-content">
                  <p>Aucune publication ou retweet pour le moment.</p>
                </div>
              </div>
            );
          }
          
          return allContent.map((item, index) => {
            if (item.type === 'publication') {
              const pub = item.content;
              return (
                <article className="publication" key={`pub-${pub.id}`}>
                  <div className="publication-header">
                    <span className="publication-author">{pub.user?.username || 'Utilisateur inconnu'}</span>
                    <span className="publication-username">@{pub.user?.username || 'user'}</span>
                    <span className="publication-date">· {pub.createdAt ? new Date(pub.createdAt).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) : 'Date inconnue'}</span>
                  </div>
                  <div className="publication-content">
                    {pub.texte || pub.content || 'AUCUN CONTENU'}
                    {pub.image && (
                      <div className="publication-media">
                        <img src={pub.image.startsWith('http') ? pub.image : `/uploads/images/${pub.image}`} alt="Image" style={{ maxWidth: '100%', borderRadius: '12px', marginTop: '10px' }} />
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
                  </div>
                  <div className="publication-comments">
                    <CommentForm publicationId={pub.id} onSuccess={() => {}} />
                    <CommentList publicationId={pub.id} />
                  </div>
                </article>
              );
            } else {
              const repost = item.content;
              return (
                <article className="publication reposted" key={`repost-${repost.id}`} style={{borderLeft:'4px solid #4caf50',background:'#f6fff6'}}>
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
                        <img src={repost.publication.image.startsWith('http') ? repost.publication.image : `/uploads/images/${repost.publication.image}`} alt="Image" style={{ maxWidth: '100%', borderRadius: '12px', marginTop: '10px' }} />
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
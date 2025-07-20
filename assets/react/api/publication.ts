import axios from 'axios';

const API_URL = '/api';

export const fetchPublications = async (token?: string) => {
  try {
    if (token) {
      // Utiliser l'endpoint avec les likes de l'utilisateur courant
      const res = await axios.get(`${API_URL}/publications-with-likes`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      return res.data['hydra:member'] || res.data.member || [];
    } else {
      // Fallback vers l'endpoint standard
      const res = await axios.get(`${API_URL}/publications`);
      return res.data['hydra:member'] || res.data.member || [];
    }
  } catch (error) {
    console.error('Error fetching publications:', error);
    // En cas d'erreur, essayer l'endpoint standard
    try {
      const res = await axios.get(`${API_URL}/publications`);
      return res.data['hydra:member'] || res.data.member || [];
    } catch (fallbackError) {
      console.error('Fallback error:', fallbackError);
      return [];
    }
  }
};

export const createPublication = async (data: FormData, token: string) => {
  const res = await axios.post(`${API_URL}/publications/upload`, data, {
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'multipart/form-data',
    },
  });
  return res.data;
};

export const likePublication = async (publicationId: number, token: string) => {
  const res = await axios.post(`${API_URL}/likes`, { publication: `/api/publications/${publicationId}` }, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return res.data;
};

export const unlikePublication = async (publicationId: number, token: string) => {
  try {
    // On récupère le Like de l'utilisateur courant pour cette publication
    const res = await axios.get(`/api/likes/user/${publicationId}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    const like = (res.data['hydra:member'] || res.data.member || res.data || [])[0];
    if (like && like['@id']) {
      await axios.delete(like['@id'], {
        headers: { Authorization: `Bearer ${token}` },
      });
      return true;
    }
    return false;
  } catch (error) {
    console.error('Error in unlikePublication:', error);
    return false;
  }
};

export const commentPublication = async (publicationId: number, content: string, token: string) => {
  const res = await axios.post(`${API_URL}/commentaires`, {
    publication: `/api/publications/${publicationId}`,
    content,
  }, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return res.data;
};

export const repostPublication = async (publicationId: number, token: string) => {
  const res = await axios.post(`${API_URL}/reposts`, { publication: `/api/publications/${publicationId}` }, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return res.data;
};

export const likeComment = async (commentId: number, token: string) => {
  const res = await axios.post(`/api/comment_likes`, { commentaire: `/api/commentaires/${commentId}` }, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return res.data;
};

export const unlikeComment = async (commentId: number, token: string) => {
  // On récupère le CommentLike de l'utilisateur courant pour ce commentaire
  const res = await axios.get(`/api/comment_likes?user=current&commentaire[]=${commentId}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  const like = (res.data['hydra:member'] || res.data.member || res.data || [])[0];
  if (like && like['@id']) {
    await axios.delete(like['@id'], {
      headers: { Authorization: `Bearer ${token}` },
    });
    return true;
  }
  return false;
};

export const followUser = async (userId: number, token: string) => {
  try {
    const res = await axios.post(`/api/follows`, { followed: `/api/users/${userId}` }, {
      headers: { Authorization: `Bearer ${token}` },
    });
    console.log('DEBUG FOLLOW - Follow created/retrieved:', res.data);
    return res.data;
  } catch (error) {
    console.error('DEBUG FOLLOW - Error following user:', error);
    throw error;
  }
};

export const unfollowUser = async (userId: number, token: string, currentUserId?: number) => {
  try {
    let followerId = currentUserId;
    
    // Si currentUserId n'est pas fourni, essayer de le récupérer depuis le token
    if (!followerId) {
      try {
        const userRes = await axios.get('/api/users/me', {
          headers: { Authorization: `Bearer ${token}` },
        });
        followerId = userRes.data.id;
      } catch (error) {
        console.error('DEBUG UNFOLLOW - Erreur récupération utilisateur:', error);
        throw new Error('Impossible de récupérer l\'utilisateur connecté');
      }
    }
    
    // Ensuite, récupérer le Follow de l'utilisateur courant pour ce user
    const res = await axios.get(`/api/follows?follower=${followerId}&followed=${userId}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    
    console.log('DEBUG UNFOLLOW - Follow trouvé:', res.data);
    const follow = (res.data['hydra:member'] || res.data.member || res.data || [])[0];
    
    if (follow && follow['@id']) {
      console.log('DEBUG UNFOLLOW - Suppression du follow:', follow['@id']);
      await axios.delete(follow['@id'], {
        headers: { Authorization: `Bearer ${token}` },
      });
      return true;
    } else {
      console.log('DEBUG UNFOLLOW - Aucun follow trouvé');
      return false;
    }
  } catch (error) {
    console.error('DEBUG UNFOLLOW - Erreur:', error);
    throw error;
  }
};

export const isFollowingUser = async (userId: number, token: string, currentUserId?: number) => {
  try {
    let followerId = currentUserId;
    
    // Si currentUserId n'est pas fourni, essayer de le récupérer depuis le token
    if (!followerId) {
      try {
        const userRes = await axios.get('/api/users/me', {
          headers: { Authorization: `Bearer ${token}` },
        });
        followerId = userRes.data.id;
      } catch (error) {
        console.error('DEBUG IS_FOLLOWING - Erreur récupération utilisateur:', error);
        return false;
      }
    }
    
    // Ensuite, récupérer le Follow de l'utilisateur courant pour ce user
    const res = await axios.get(`/api/follows?follower=${followerId}&followed=${userId}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    
    const follow = (res.data['hydra:member'] || res.data.member || res.data || [])[0];
    return !!follow;
  } catch (error) {
    console.error('DEBUG IS_FOLLOWING - Erreur:', error);
    return false;
  }
};

export const fetchRepostsByUser = async (userId: number) => {
  const res = await axios.get(`/api/reposts?user=${userId}`);
  return res.data['hydra:member'] || res.data.member || [];
};

export const deleteRepost = async (repostId: number, token: string) => {
  await axios.delete(`/api/reposts/${repostId}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return true;
}; 
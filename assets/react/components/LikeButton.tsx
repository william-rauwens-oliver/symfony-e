import React, { useState, useEffect } from 'react';
import { likePublication, unlikePublication } from '../api/publication';
import { useUser } from '../context/UserContext';
import IconLike from '../assets/IconLike.svg';
import './LikeButton.css';

interface Props {
  publicationId: number;
  initialCount?: number;
  initiallyLiked?: boolean;
  onLike?: () => void;
}

const LikeButton: React.FC<Props> = ({ publicationId, initialCount = 0, initiallyLiked = false, onLike }) => {
  const { token } = useUser();
  const [liked, setLiked] = useState(initiallyLiked);
  // Synchronise le state avec la prop à chaque changement (ex: refresh parent)
  useEffect(() => {
    setLiked(initiallyLiked);
  }, [initiallyLiked]);
  const [count, setCount] = useState(initialCount);
  const [loading, setLoading] = useState(false);
  const [animate, setAnimate] = useState(false);

  const handleLike = async () => {
    if (!token || loading) return;
    
    // Optimistic update
    const wasLiked = liked;
    setLiked(!wasLiked);
    setCount(c => wasLiked ? Math.max(0, c - 1) : c + 1);
    
    setLoading(true);
    setAnimate(true);
    setTimeout(() => setAnimate(false), 400);
    
    try {
      if (wasLiked) {
        // Unlike
        await unlikePublication(publicationId, token);
      } else {
        // Like
        await likePublication(publicationId, token);
      }
      if (onLike) onLike();
    } catch (error) {
      console.error('Error toggling like:', error);
      // Revert state on error
      setLiked(wasLiked);
      setCount(c => wasLiked ? c + 1 : Math.max(0, c - 1));
    } finally {
      setLoading(false);
    }
  };

  return (
    <button className={`like-btn${liked ? ' liked' : ''}${animate ? ' pop' : ''}`} onClick={handleLike} disabled={loading}>
      <img src={IconLike} alt="Like" className="like-icon" />
      <span className="like-count">{count}</span>
    </button>
  );
};

export default LikeButton; 
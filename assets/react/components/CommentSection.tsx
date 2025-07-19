import React, { useState } from 'react';
import { commentPublication } from '../api/publication';
import { useUser } from '../context/UserContext';
import IconComment from '../assets/IconComment.svg';

interface Props {
  publicationId: number;
}

const CommentSection: React.FC<Props> = ({ publicationId }) => {
  const { token } = useUser();
  const [comment, setComment] = useState('');
  const [comments, setComments] = useState<string[]>([]);
  const [loading, setLoading] = useState(false);

  const handleComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!token || !comment) return;
    setLoading(true);
    try {
      await commentPublication(publicationId, comment, token);
      setComments(c => [...c, comment]);
      setComment('');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="comment-section">
      <form className="comment-form" onSubmit={handleComment}>
        <input
          className="comment-input"
          type="text"
          placeholder="Ajouter un commentaire..."
          value={comment}
          onChange={e => setComment(e.target.value)}
          disabled={loading}
        />
        <button className="comment-btn" type="submit" disabled={loading || !comment}>
          <img src={IconComment} alt="Commenter" />
        </button>
      </form>
      <div className="comment-list">
        {comments.map((c, i) => (
          <div className="comment-item" key={i}>{c}</div>
        ))}
      </div>
    </div>
  );
};

export default CommentSection; 
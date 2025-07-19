import React from 'react';
import IconShare from '../assets/IconShare.svg';

interface Props {
  publicationId: number;
}

const ShareButton: React.FC<Props> = ({ publicationId }) => {
  const handleShare = () => {
    const url = `${window.location.origin}/publication/${publicationId}`;
    navigator.clipboard.writeText(url);
    alert('Lien copié !');
  };
  return (
    <button className="share-btn" onClick={handleShare}>
      <img src={IconShare} alt="Partager" className="share-icon" />
    </button>
  );
};

export default ShareButton; 
import React from 'react';

interface ModalProps {
  open: boolean;
  onClose: () => void;
  title?: string;
  children: React.ReactNode;
}

const Modal: React.FC<ModalProps> = ({ open, onClose, title, children }) => {
  if (!open) return null;
  return (
    <div style={{
      position: 'fixed',
      top: 0,
      left: 0,
      width: '100vw',
      height: '100vh',
      background: 'rgba(0,0,0,0.3)',
      zIndex: 1000,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
    }} onClick={onClose}>
      <div style={{
        background: '#fff',
        borderRadius: 12,
        minWidth: 320,
        maxWidth: 400,
        padding: 24,
        boxShadow: '0 4px 32px rgba(0,0,0,0.18)',
        position: 'relative',
      }} onClick={e => e.stopPropagation()}>
        <button onClick={onClose} style={{
          position: 'absolute',
          top: 12,
          right: 12,
          background: 'none',
          border: 'none',
          fontSize: 22,
          cursor: 'pointer',
        }} aria-label="Fermer">×</button>
        {title && <h2 style={{marginTop:0, marginBottom:16, fontSize:22}}>{title}</h2>}
        <div>{children}</div>
      </div>
    </div>
  );
};

export default Modal; 
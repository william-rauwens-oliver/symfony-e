import React from 'react';
import { useFlash } from '../context/FlashContext';

const colors = {
  success: '#27ae60',
  error: '#e0245e',
  info: '#1da1f2',
};

const FlashMessage: React.FC = () => {
  const { flash, setFlash } = useFlash();
  if (!flash) return null;
  return (
    <div
      style={{
        position: 'fixed',
        top: 24,
        left: '50%',
        transform: 'translateX(-50%)',
        background: colors[flash.type],
        color: '#fff',
        padding: '14px 32px',
        borderRadius: 8,
        fontWeight: 600,
        fontSize: 17,
        zIndex: 2000,
        boxShadow: '0 4px 24px rgba(0,0,0,0.13)',
        minWidth: 220,
        textAlign: 'center',
        cursor: 'pointer',
      }}
      onClick={() => setFlash(null)}
      role="alert"
      aria-live="assertive"
    >
      {flash.message}
    </div>
  );
};

export default FlashMessage; 
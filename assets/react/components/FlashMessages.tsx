import React from 'react';

export type FlashType = 'success' | 'error' | 'warning' | 'info';

export interface FlashMessage {
  label: FlashType;
  message: string;
  id: string;
}

interface FlashMessagesProps {
  messages: FlashMessage[];
  onClose: (id: string) => void;
}

const iconFor = (label: FlashType) => {
  switch (label) {
    case 'success': return '✅';
    case 'error': return '❌';
    case 'warning': return '⚠️';
    case 'info': return 'ℹ️';
    default: return '';
  }
};

const FlashMessages: React.FC<FlashMessagesProps> = ({ messages, onClose }) => (
  <>
    {messages.map(({ label, message, id }) => (
      <div className={`flash-message flash-${label}`} key={id}>
        <div className="flash-content">
          <span className="flash-icon">{iconFor(label)}</span>
          <span className="flash-text">{message}</span>
          <button className="flash-close" onClick={() => onClose(id)}>&times;</button>
        </div>
      </div>
    ))}
  </>
);

export default FlashMessages; 
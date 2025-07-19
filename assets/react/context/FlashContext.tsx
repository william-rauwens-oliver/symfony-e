import React, { createContext, useContext, useState } from 'react';
import type { ReactNode } from 'react';

export type FlashType = 'success' | 'error' | 'info';

interface FlashMessage {
  type: FlashType;
  message: string;
}

interface FlashContextProps {
  flash: FlashMessage | null;
  setFlash: (msg: FlashMessage | null) => void;
  showFlash: (type: FlashType, message: string, duration?: number) => void;
}

const FlashContext = createContext<FlashContextProps | undefined>(undefined);

export const FlashProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [flash, setFlash] = useState<FlashMessage | null>(null);

  const showFlash = (type: FlashType, message: string, duration = 3500) => {
    setFlash({ type, message });
    if (duration > 0) {
      setTimeout(() => setFlash(null), duration);
    }
  };

  return (
    <FlashContext.Provider value={{ flash, setFlash, showFlash }}>
      {children}
    </FlashContext.Provider>
  );
};

export function useFlash() {
  const ctx = useContext(FlashContext);
  if (!ctx) throw new Error('useFlash must be used within a FlashProvider');
  return ctx;
} 
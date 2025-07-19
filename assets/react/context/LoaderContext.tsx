import React, { createContext, useContext, useState } from 'react';
import type { ReactNode } from 'react';

interface LoaderContextProps {
  isLoading: boolean;
  setLoading: (loading: boolean) => void;
  showLoader: () => void;
  hideLoader: () => void;
}

const LoaderContext = createContext<LoaderContextProps | undefined>(undefined);

export const LoaderProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [isLoading, setIsLoading] = useState(false);

  const showLoader = () => setIsLoading(true);
  const hideLoader = () => setIsLoading(false);

  return (
    <LoaderContext.Provider value={{ isLoading, setLoading: setIsLoading, showLoader, hideLoader }}>
      {children}
    </LoaderContext.Provider>
  );
};

export function useLoader() {
  const ctx = useContext(LoaderContext);
  if (!ctx) throw new Error('useLoader must be used within a LoaderProvider');
  return ctx;
} 
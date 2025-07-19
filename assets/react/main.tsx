console.log('DEBUG MAIN - JavaScript file loaded');

import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';
import './index.css';
import { UserProvider } from './context/UserContext';

console.log('DEBUG MAIN - Starting React app mount');
console.log('DEBUG MAIN - Root element:', document.getElementById('root'));
console.log('DEBUG MAIN - Root element content:', document.getElementById('root')?.innerHTML);

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <UserProvider>
      <App />
    </UserProvider>
  </React.StrictMode>
);

console.log('DEBUG MAIN - React app mount completed');

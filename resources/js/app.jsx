import './bootstrap';
import React from 'react';
import ReactDOM from 'react-dom/client';
import TestComponent from './components/Test';

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <TestComponent />
  </React.StrictMode>
);
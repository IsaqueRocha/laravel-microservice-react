import { Box } from '@material-ui/core';
import React from 'react';
import { BrowserRouter } from 'react-router-dom';
import './App.css';
import Breadcrumbs from './components/Breadcrumb';
import { Navbar } from './components/Navbar';
import AppRouter from './routes/AppRouter';

const App: React.FC = () => {
  return (
    <React.Fragment>
      <BrowserRouter>
        <Navbar />
        <Box paddingTop={'70px'}>
          <Breadcrumbs />
          <AppRouter />
        </Box>
      </BrowserRouter>
    </React.Fragment>
  );
};

export default App;

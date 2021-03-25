import {
  AppBar,
  Button,
  makeStyles,
  Theme,
  Toolbar,
  Typography
} from '@material-ui/core';
import React from 'react';
import logo from '../../static/img/logo.png';
import Menu from './Menu';

const useStyles = makeStyles((theme: Theme) => ({
  toolbar: {
    backgroundColor: '#000',
  },
  title: {
    flexGrow: 1,
    textAlign: 'center',
  },
  logo: {
    width: 100,
    [theme.breakpoints.up('sm')]: {
      width: 170,
    },
  },
  button: {
    color: 'inherit',
  },
}));

export const Navbar: React.FC = () => {
  const classes = useStyles();

  return (
    <div>
      <AppBar>
        <Toolbar className={classes.toolbar}>
          <Menu />
          <Typography className={classes.title}>
            <img className={classes.logo} src={logo} alt='CodeFlix' />
          </Typography>
          <Button className={classes.button}>Login</Button>
        </Toolbar>
      </AppBar>
    </div>
  );
};

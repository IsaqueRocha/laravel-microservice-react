import React, { useState } from 'react';
import { IconButton, Menu as MuiMenu, MenuItem } from '@material-ui/core';
import { Link } from 'react-router-dom';
import MenuIcon from '@material-ui/icons/Menu';
import routes, { MyRouteProps } from '../../routes';

const listRoutes = ['dashboard', 'categories.list'];
const menuRoutes = routes.filter((route) => listRoutes.includes(route.name));

const Menu = () => {
  const [anchorEl, setAnchorEl] = useState(null);

  const handleOpen = (event: any) => setAnchorEl(event.currentTarget);
  const handleClose = () => setAnchorEl(null);

  const open = Boolean(anchorEl);

  return (
    <React.Fragment>
      <IconButton
        edge='start'
        color='inherit'
        aria-label='open drawer'
        aria-controls='menu-appbar'
        aria-haspopup='true'
        onClick={handleOpen}
      >
        <MenuIcon />
      </IconButton>
      <MuiMenu
        id='menu-appbar'
        open={open}
        anchorEl={anchorEl}
        onClose={handleClose}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
        transformOrigin={{ vertical: 'top', horizontal: 'center' }}
        getContentAnchorEl={null}
      >
        {listRoutes.map((routeName, key) => {
          const route = menuRoutes.find(
            (route) => route.name === routeName
          ) as MyRouteProps;

          return (
            <MenuItem
              key={key}
              component={Link}
              to={route.path as string}
              onClick={handleClose}
            >
              {route.label}
            </MenuItem>
          );
        })}
      </MuiMenu>
    </React.Fragment>
  );
};

export default Menu;

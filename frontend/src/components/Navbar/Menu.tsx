import { IconButton, MenuItem, Menu as MuiMenu } from '@material-ui/core';
import MenuIcon from '@material-ui/icons/Menu';
import React, { useState } from 'react';

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
        <MenuItem>Categorias</MenuItem>
      </MuiMenu>
    </React.Fragment>
  );
};

export default Menu;

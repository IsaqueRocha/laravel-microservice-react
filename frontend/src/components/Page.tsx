import { Container, makeStyles, Typography } from '@material-ui/core';
import React from 'react';

const useStyles = makeStyles({
  title: {
    color: '#999',
  },
});

type PageProps = {
  title: string;
};

const Page: React.FC<PageProps> = (props) => {
  const classes = useStyles();

  return (
    <Container>
      <Typography className={classes.title} component='h1' variant='h5'>
        {props.title}
      </Typography>
      {props.children}
    </Container>
  );
};

export default Page;

// filepath: public/backoffice/api/list.js
import express from 'express';
import { pool } from './db.js';
import { StatusCodes } from 'http-status-codes';

const router = express.Router();

// GET all jaquettes
router.get('/', async (req, res) => {
  try {
    const [rows] = await pool.query('SELECT version_name, filename FROM jaquettes');
    res.json(rows);
  } catch (err) {
    res.status(StatusCodes.INTERNAL_SERVER_ERROR).send('Erreur serveur');
  }
});

export default router;

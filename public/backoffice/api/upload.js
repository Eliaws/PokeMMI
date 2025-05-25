import express from 'express';
import multer from 'multer';
import { sanitizeFilename } from './sanitize.js';
import { pool } from './db.js'; // DB pool depuis .env
import path from 'path';
import { mkdirSync } from 'fs';

// Directory for uploaded covers under backoffice
const uploadsDir = path.join(process.cwd(), 'public', 'backoffice', 'uploads');

// Ensure uploads directory exists
mkdirSync(uploadsDir, { recursive: true });

// Use absolute uploadsDir for multer
const upload = multer({ dest: uploadsDir });

const router = express.Router();

const HTTP_BAD_REQUEST = 400;

router.post('/', upload.single('jaquette'), async (req, res) => {
  const { version } = req.body;
  const file = req.file;

  if (!file || !version) return res.status(HTTP_BAD_REQUEST).send('Fichier ou version manquant');

  // Création de la table si elle n'existe pas
  await pool.query(`
    CREATE TABLE IF NOT EXISTS jaquettes (
      id INT AUTO_INCREMENT PRIMARY KEY,
      version_name VARCHAR(255) NOT NULL,
      filename VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  `);

  const sanitized = sanitizeFilename(file.originalname);
  const fsPromises = await import('fs/promises');
  const oldPath = file.path;
  const newPath = path.join(uploadsDir, sanitized);
  await fsPromises.rename(oldPath, newPath);

  await pool.query('INSERT INTO jaquettes (version_name, filename) VALUES (?, ?)', [version, sanitized]);
  res.send('Upload réussi');
});

export default router;

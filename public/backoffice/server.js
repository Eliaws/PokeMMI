// filepath: public/backoffice/server.js
import express from 'express';
import path from 'path';
import { fileURLToPath } from 'url';
import uploadRouter from './api/upload.js';
import listRouter from './api/list.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const app = express();
const port = process.env.PORT || 3000;

// Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// API routes
app.use('/api/upload', uploadRouter);
app.use('/api/list', listRouter);

// Static files
app.use(express.static(__dirname));

// Fallback to index.html
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

app.listen(port, () => console.log(`Backoffice listening at http://localhost:${port}`));

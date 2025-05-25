import mysql from 'mysql2/promise';
import dotenv from 'dotenv';

dotenv.config();

export const pool = mysql.createPool({
  host: process.env.VITE_DB_HOST,
  user: process.env.VITE_DB_USER,
  password: process.env.VITE_DB_PASS,
  database: process.env.VITE_DB_NAME,
});

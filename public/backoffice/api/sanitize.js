import path from 'path';

// Sanitize filename: remove accents, spaces, special chars, to lowercase, replace with hyphens
export function sanitizeFilename(originalName) {
  const { name: baseName, ext } = path.parse(originalName);
  const sanitizedBase = baseName
    .toLowerCase()
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
  return `${sanitizedBase}${ext.toLowerCase()}`;
}

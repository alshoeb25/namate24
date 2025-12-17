import CryptoJS from 'crypto-js';

const SECRET_KEY = 'namate24-secret-key-2024'; // Should be from env in production

export function encryptQueryParams(params) {
  try {
    const jsonString = JSON.stringify(params);
    const encrypted = CryptoJS.AES.encrypt(jsonString, SECRET_KEY).toString();
    return encodeURIComponent(encrypted);
  } catch (error) {
    console.error('Encryption error:', error);
    return null;
  }
}

export function decryptQueryParams(encryptedData) {
  try {
    const decrypted = CryptoJS.AES.decrypt(decodeURIComponent(encryptedData), SECRET_KEY);
    const jsonString = decrypted.toString(CryptoJS.enc.Utf8);
    return JSON.parse(jsonString);
  } catch (error) {
    console.error('Decryption error:', error);
    return null;
  }
}

export function encryptString(text) {
  try {
    return CryptoJS.AES.encrypt(text, SECRET_KEY).toString();
  } catch (error) {
    console.error('Encryption error:', error);
    return text;
  }
}

export function decryptString(encryptedText) {
  try {
    const decrypted = CryptoJS.AES.decrypt(encryptedText, SECRET_KEY);
    return decrypted.toString(CryptoJS.enc.Utf8);
  } catch (error) {
    console.error('Decryption error:', error);
    return encryptedText;
  }
}

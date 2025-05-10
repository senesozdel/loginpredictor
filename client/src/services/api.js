// API URL
const API_BASE_URL = process.env.REACT_APP_API_BASE_URL || "https://loginpredictor-api.onrender.com/api";

// Belirli bir kullanıcı için tahminleri getirir
export const getUserPredictions = async (userId) => {
  try {
    const response = await fetch(`${API_BASE_URL}/users/${userId}/predictions`);
    if (!response.ok) {
      throw new Error(`API hatası: ${response.status}`);
    }
    return await response.json();
  } catch (error) {
    console.error('Tahmin verisi alınamadı:', error);
    throw error;
  }
};

// Tüm kullanıcıları {id,name} şeklinde getirir
export const getAllUsers = async () => {
  try {
    const response = await fetch(`${API_BASE_URL}/usernames`);
    if (!response.ok) {
      throw new Error(`API hatası: ${response.status}`);
    }
    const result = await response.json();
    return result.data
  } catch (error) {
    console.error('Kullanıcı verisi alınamadı:', error);
    throw error;
  }
};

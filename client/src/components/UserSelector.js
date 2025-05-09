import { useState, useEffect } from 'react';
import { getAllUsers } from '../services/api';

const UserSelector = ({ onUserChange, selectedUser }) => {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchUsers = async () => {
      setLoading(true);
      setError(null);
      
      try {
        const userData = await getAllUsers();
        setUsers(userData);
      } catch (err) {
        setError('Kullanıcı listesi yüklenemedi.');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchUsers();
  }, []);

  return (
    <div className="card">
      <div className="card-body">
        <h5 className="card-title">Kullanıcı Seçin</h5>
        <div className="form-group">
          {loading ? (
            <div className="d-flex align-items-center">
              <div className="spinner-border spinner-border-sm me-2" role="status">
                <span className="visually-hidden">Yükleniyor...</span>
              </div>
              <span>Kullanıcılar yükleniyor...</span>
            </div>
          ) : error ? (
            <div className="alert alert-danger mb-0">{error}</div>
          ) : (
            <select 
              className="form-select" 
              value={selectedUser} 
              onChange={(e) => onUserChange(e.target.value)}
              aria-label="Kullanıcı seçimi"
            >
              {users.length === 0 ? (
                <option value="">Kullanıcı bulunamadı</option>
              ) : (
                users.map((user) => (
                  <option key={user.id} value={user.id}>
                    {user.name} - ({user.id})
                  </option>
                ))
              )}
            </select>
          )}
        </div>
      </div>
    </div>
  );
};

export default UserSelector;
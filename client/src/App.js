import  { useState, useEffect } from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import PredictionTable from './components/PredictionTable';
import UserSelector from './components/UserSelector';
import { getUserPredictions } from './services/api';

function App() {
  const [predictions, setPredictions] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [selectedUser, setSelectedUser] = useState('user_3'); // Default olarak user_3

  useEffect(() => {
    const fetchPredictions = async () => {
      setLoading(true);
      setError(null);
      try {
        const data = await getUserPredictions(selectedUser);
        setPredictions(data);
      } catch (err) {
        setError('Tahmin verileri yüklenemedi. Lütfen daha sonra tekrar deneyin.');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchPredictions();
  }, [selectedUser]);

  const handleUserChange = (userId) => {
    setSelectedUser(userId);
  };

  return (
    <div className="container-fluid">
      <header className="bg-dark text-white text-center py-4 mb-4">
        <h1 className="display-5">Login Predictor</h1>
        <p className="lead">Algorithmic Comparison </p>
      </header>

      <main className="container">
        <div className="row justify-content-center mb-4">
          <div className="col-md-6">
            <UserSelector onUserChange={handleUserChange} selectedUser={selectedUser} />
          </div>
        </div>
        
        {loading && (
          <div className="text-center my-5">
            <div className="spinner-border text-primary" role="status">
              <span className="visually-hidden">Yükleniyor...</span>
            </div>
            <p className="mt-2">Veriler yükleniyor...</p>
          </div>
        )}
        
        {error && (
          <div className="alert alert-danger" role="alert">
            {error}
          </div>
        )}
        
        {!loading && !error && predictions && (
          <div className="row">
            <div className="col-12">
              <PredictionTable predictions={predictions} />
            </div>
          </div>
        )}
      </main>

      <footer className="bg-light text-center text-muted py-4 mt-5">
        <p className="mb-0">Login Prediction Senesozdel &copy; 2025</p>
      </footer>
    </div>
  );
}

export default App;
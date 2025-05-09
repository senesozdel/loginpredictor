const PredictionTable = ({ predictions }) => {
  // Veri olmadığında gösterilecek içerik
  if (!predictions) {
    return <div className="alert alert-info">Henüz tahmin verisi yok</div>;
  }
  
  // Tarih formatlama metodu
  const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('tr-TR', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  // Farklı tarihleri karşılaştırma metodu
  const getDateDifferenceClass = (predictionDate, lastLoginDate) => {
    if (!predictionDate || !lastLoginDate) return '';
    
    const prediction = new Date(predictionDate);
    const lastLogin = new Date(lastLoginDate);
    const diffInDays = Math.round((prediction - lastLogin) / (1000 * 60 * 60 * 24));
    
    if (diffInDays <= 2) return 'table-warning'; 
    return 'table-success'; 
};

  return (
    <div className="card shadow-sm">
      <div className="card-header bg-primary text-white">
        <h4 className="mb-0">Kullanıcı Login Tahminleri</h4>
      </div>
      <div className="card-body">
        <div className="table-responsive">
          <table className="table table-bordered table-hover">
            <thead className="table-dark">
              <tr>
                <th>Kullanıcı ID</th>
                <th>Son Login</th>
                <th>Ortalama Aralık Yöntemi</th>
                <th>Gün ve Saat Paterni Yöntemi</th>
                <th>Ağırlıklı Son Login Yöntemi</th>
                <th>Günlük Rutin Yöntemi</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td className="fw-bold">{predictions.userId}</td>
                <td>{formatDate(predictions.lastLogin)}</td>
                <td className={getDateDifferenceClass(predictions.predictions["Ortalama Aralık Yöntemi"], predictions.lastLogin)}>
                  {formatDate(predictions.predictions["Ortalama Aralık Yöntemi"])}
                </td>
                <td className={getDateDifferenceClass(predictions.predictions["Gün ve Saat Paterni Yöntemi"], predictions.lastLogin)}>
                  {formatDate(predictions.predictions["Gün ve Saat Paterni Yöntemi"])}
                </td>
                <td className={getDateDifferenceClass(predictions.predictions["Ağırlıklı Son Login Yöntemi"], predictions.lastLogin)}>
                  {formatDate(predictions.predictions["Ağırlıklı Son Login Yöntemi"])}
                </td>
                <td className={getDateDifferenceClass(predictions.predictions["Günlük Rutin Yöntemi"], predictions.lastLogin)}>
                  {formatDate(predictions.predictions["Günlük Rutin Yöntemi"])}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div className="mt-3">
          <h5>Tarih Renk Kodları</h5>
          <div className="d-flex gap-3">
            <div>
              <span className="badge bg-success">Yeşil</span>: 2+ gün sonra
            </div>
            <div>
              <span className="badge bg-warning text-dark">Sarı</span>: 0-2 gün içinde
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PredictionTable;
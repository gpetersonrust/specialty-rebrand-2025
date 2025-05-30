import React, { useState, useEffect } from 'react';

const DescriptionsPanel = ({ setActivePanel, selectedSpecialty, setSelectedSpecialty }) => {
  const [specialties, setSpecialties] = useState([]);
  const [description, setDescription] = useState('');
  const [descriptionsData, setDescriptionsData] = useState([]);
  const [saveStatus, setSaveStatus] = useState(null); // 'saving', 'saved', 'error'
  const nonce = specialtyRebrandData?.nonce;

  // Load specialties
  useEffect(() => {
    fetch('/wp-json/specialty-rebrand/v1/specialties', {
      headers: { 'X-WP-Nonce': nonce },
    })
      .then(res => res.json())
      .then(data => setSpecialties(data))
      .catch(err => console.error('Error loading specialties', err));
  }, []);

  // Load saved descriptions
  useEffect(() => {
    fetch('/wp-json/specialty-rebrand/v1/descriptions', {
      headers: { 'X-WP-Nonce': nonce },
    })
      .then(res => res.json())
      .then(data => {
        if (Array.isArray(data)) {
          setDescriptionsData(data);
        } else {
          console.warn('Invalid descriptions format', data);
        }
      })
      .catch(err => console.error('Error loading saved descriptions', err));
  }, []);

  // Update textarea when specialty is selected
  useEffect(() => {
    if (selectedSpecialty) {
      const existing = descriptionsData.find(d => d.term === selectedSpecialty.name);
      setDescription(existing ? existing.text : '');
    }
  }, [selectedSpecialty, descriptionsData]);

  const handleSave = () => {
    if (!selectedSpecialty || !description.trim()) return;

    const updated = [...descriptionsData];
    const index = updated.findIndex(d => d.term === selectedSpecialty.name);
    if (index > -1) {
      updated[index].text = description;
    } else {
      updated.push({ term: selectedSpecialty.name, text: description });
    }

    setSaveStatus('saving');

    fetch('/wp-json/specialty-rebrand/v1/descriptions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce,
      },
      body: JSON.stringify(updated),
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          setDescriptionsData(data.data); // use sanitized return
          setSaveStatus('saved');
          setTimeout(() => setSaveStatus(null), 2000);
        } else {
          console.error('Save failed:', data.message || data);
          setSaveStatus('error');
        }
      })
      .catch(err => {
        console.error('Request error', err);
        setSaveStatus('error');
      });
  };

  return (
    <div className="descriptions-panel">
      <h2 className="title">Specialty Descriptions</h2>

      <select
        className="specialty-select"
        onChange={(e) => {
          const term = specialties.find(s => s.id === parseInt(e.target.value));
          setSelectedSpecialty(term);
        }}
        value={selectedSpecialty?.id || ''}
      >
        <option value="">Select a Specialty</option>
        {specialties.map(s => (
          <option key={s.id} value={s.id}>{s.name}</option>
        ))}
      </select>

      {selectedSpecialty && (
        <>
          <textarea
            className="description-textarea"
            placeholder="Enter description"
            value={description}
            onChange={(e) => setDescription(e.target.value)}
          />
          <button
            className="save-button"
            onClick={handleSave}
            disabled={!description.trim()}
          >
            Save
          </button>
          {saveStatus === 'saving' && <p className="text-gray-600 mt-2">Saving…</p>}
          {saveStatus === 'saved' && <p className="text-green-600 mt-2">Saved successfully!</p>}
          {saveStatus === 'error' && <p className="text-red-600 mt-2">Failed to save. Check console.</p>}
        </>
      )}

      <div className="json-preview mt-6">
        <h3 className="preview-title">Current Descriptions JSON</h3>
        <pre className="preview-content">
          {JSON.stringify(descriptionsData, null, 2)}
        </pre>
      </div>
    </div>
  );
};

export default DescriptionsPanel;

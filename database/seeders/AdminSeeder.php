import { useState, useEffect } from "react"; // useEffect import kia
import axios from "axios";
import { useNavigate } from "react-router-dom";
export default function Register() {
    const navigate = useNavigate();
    // States
    const [name, setName] = useState("");
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [role, setRole] = useState("patient");
   
    const [specialization, setSpecialization] = useState("");
    const [customSpec, setCustomSpec] = useState("");
    const [error, setError] = useState("");
    // 👇 1. Dynamic List State
    // Default list hamesha rahay gi
    const defaultSpecs = [
        "General Physician", "Cardiologist", "Dentist",
        "Dermatologist", "Neurologist", "Orthopedic"
    ];
    const [availableSpecs, setAvailableSpecs] = useState(defaultSpecs);
    // 👇 2. Database se Purani Categories Lana
    useEffect(() => {
        const fetchSpecs = async () => {
            try {
                const response = await axios.get("http://127.0.0.1:8000/api/specializations");
                const dbSpecs = response.data;
                // --- MERGE LOGIC ---
                // Default list + Database list ko mila kar 'Set' banaya taake duplicates hat jayen
                const uniqueSpecs = [...new Set([...defaultSpecs, ...dbSpecs])];
                setAvailableSpecs(uniqueSpecs);
            } catch (err) {
                console.error("Failed to fetch specs", err);
            }
        };
        fetchSpecs();
    }, []); // Sirf aik dafa chalay ga jab page load hoga
    const handleRegister = async (e) => {
        e.preventDefault();
        setError("");
        let finalSpecialization = null;
        if (role === 'doctor') {
            if (specialization === "Other") {
                if (!customSpec.trim()) {
                    setError("Please type your specialization");
                    return;
                }
                // Capitalize first letter (Cleaning)
                finalSpecialization = customSpec.charAt(0).toUpperCase() + customSpec.slice(1);
            } else {
                finalSpecialization = specialization;
            }
        }
        try {
            await axios.post("http://127.0.0.1:8000/api/register", {
                name, email, password, role,
                specialization: finalSpecialization
            });
            alert("Registration Successful! Please Login.");
            navigate("/login");
        } catch (err) {
            console.error(err);
            setError(err.response?.data?.message || "Registration Failed");
        }
    };
    return (
        <div 
            style={{ 
                height: "100vh", 
                background: "linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%)", 
                display: "flex", 
                alignItems: "center", 
                justifyContent: "center", 
                fontFamily: "'Segoe UI', sans-serif",
                position: "fixed",
                top: 0,
                left: 0,
                right: 0,
                bottom: 0,
                padding: "0",
                margin: 0,
                overflow: "hidden"
            }}
        >
            <div 
                style={{ 
                    maxWidth: "400px", 
                    width: "100%", 
                    maxHeight: "100vh",
                    background: "rgba(255, 255, 255, 0.95)", 
                    padding: "40px", 
                    borderRadius: "12px", 
                    boxShadow: "0 10px 30px rgba(0,0,0,0.3)", 
                    overflowY: "auto",
                    boxSizing: "border-box"
                }}
            >
                <h2 style={{ textAlign: "center", color: "#333", marginBottom: "30px" }}>Create Account</h2>
               
                {error && <div style={{ color: "#dc3545", textAlign: "center", background: "#f8d7da", padding: "10px", borderRadius: "8px", marginBottom: "20px" }}>{error}</div>}
                <form onSubmit={handleRegister} style={{ display: "flex", flexDirection: "column", gap: "15px" }}>
                   
                    <input type="text" placeholder="Full Name" value={name} onChange={(e) => setName(e.target.value)} required style={inputStyle} />
                    <input type="email" placeholder="Email" value={email} onChange={(e) => setEmail(e.target.value)} required style={inputStyle} />
                    <input type="password" placeholder="Password" value={password} onChange={(e) => setPassword(e.target.value)} required style={inputStyle} />
                    <select value={role} onChange={(e) => setRole(e.target.value)} style={inputStyle}>
                        <option value="patient">Patient</option>
                        <option value="doctor">Doctor</option>
                    </select>
                    {role === 'doctor' && (
                        <div style={{ background: "#e3f2fd", padding: "15px", borderRadius: "8px" }}>
                            <label style={{ fontWeight: "600", color: "#0c5460", marginBottom: "5px", display: "block" }}>Select Specialization</label>
                           
                            <select
                                value={specialization}
                                onChange={(e) => setSpecialization(e.target.value)}
                                style={{...inputStyle, marginBottom: specialization === "Other" ? "10px" : "0"}}
                            >
                                <option value="">-- Select Category --</option>
                               
                                {/* 👇 3. Dynamic Mapping (Ab ye list mix ho kar aa rahi hai) */}
                                {availableSpecs.map((spec, index) => (
                                    <option key={index} value={spec}>{spec}</option>
                                ))}
                                <option value="Other" style={{fontWeight: "bold", color: "blue"}}>+ Add New (Other)</option>
                            </select>
                            {specialization === "Other" && (
                                <input
                                    type="text"
                                    placeholder="Type e.g. Eye Specialist"
                                    value={customSpec}
                                    onChange={(e) => setCustomSpec(e.target.value)}
                                    style={inputStyle}
                                    autoFocus
                                />
                            )}
                        </div>
                    )}
                    <button type="submit" style={btnStyle}>Register</button>
                </form>
                <p style={{ textAlign: "center", marginTop: "20px" }}>
                    Already have an account? <span onClick={() => navigate("/login")} style={{ color: "#28a745", cursor: "pointer", fontWeight: "bold" }}>Login Here</span>
                </p>
            </div>
        </div>
    );
}
const inputStyle = { width: "100%", padding: "12px", border: "1px solid #ccc", borderRadius: "8px", boxSizing: "border-box" };
const btnStyle = { background: "#28a745", color: "white", padding: "14px", border: "none", borderRadius: "8px", cursor: "pointer", fontSize: "16px", fontWeight: "bold" };
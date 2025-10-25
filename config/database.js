const mysql = require('mysql2/promise');

// Configuración de la base de datos
const dbConfig = {
    host: process.env.DB_HOST || '54.94.131.196',
    port: process.env.DB_PORT || 3306,
    database: process.env.DB_DATABASE || 'datalake_economico',
    user: process.env.DB_USERNAME || 'estadistica',
    password: process.env.DB_PASSWORD || 'Estadistica2024!!',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
    acquireTimeout: 60000,
    timeout: 60000
};

// Pool de conexiones
const pool = mysql.createPool(dbConfig);

// Función para ejecutar consultas
async function query(sql, params = []) {
    try {
        const [results] = await pool.execute(sql, params);
        return results;
    } catch (error) {
        console.error('Database query error:', error);
        throw error;
    }
}

// Función para cerrar el pool (útil para testing)
async function closePool() {
    await pool.end();
}

module.exports = {
    query,
    closePool,
    pool
};
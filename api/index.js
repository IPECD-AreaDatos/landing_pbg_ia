const express = require('express');
const cors = require('cors');
require('dotenv').config();

const pbgService = require('../services/pbgService');

const app = express();

// Middleware
app.use(cors());
app.use(express.json());

// Función helper para respuestas consistentes
const sendResponse = (res, message, data = null, status = 200) => {
    res.status(status).json({
        message,
        data
    });
};

// Función helper para manejo de errores
const handleError = (res, error, message = 'Error interno del servidor') => {
    console.error('API Error:', error);
    res.status(500).json({
        message,
        error: process.env.NODE_ENV === 'development' ? error.message : 'Error interno'
    });
};

// Ruta raíz - información de la API
app.get('/', (req, res) => {
    sendResponse(res, 'PBG API - Producto Bruto Geográfico', {
        version: '1.0.0',
        endpoints: {
            'GET /api/pbg/charts': 'Datos agregados para gráficos del dashboard',
            'GET /api/pbg/latest': 'Datos del último año disponible',
            'GET /api/pbg/years': 'Lista de años disponibles',
            'GET /api/pbg/sectors': 'Lista de todos los sectores',
            'GET /api/pbg/sector/{codigo}': 'Datos de un sector específico'
        },
        documentation: 'https://github.com/IPECD-AreaDatos/landing_pbg_ia'
    });
});

// ============ RUTAS API PBG ============

// GET /api/pbg/charts - Datos para gráficos del dashboard
app.get('/api/pbg/charts', async (req, res) => {
    try {
        const chartData = await pbgService.getChartData();
        sendResponse(res, 'Datos de gráficos obtenidos exitosamente', chartData);
    } catch (error) {
        handleError(res, error, 'Error al obtener datos de gráficos');
    }
});

// GET /api/pbg/latest - Datos del último año disponible
app.get('/api/pbg/latest', async (req, res) => {
    try {
        const latestData = await pbgService.getLatestData();
        sendResponse(res, 'Datos del último año obtenidos exitosamente', latestData);
    } catch (error) {
        handleError(res, error, 'Error al obtener datos del último año');
    }
});

// GET /api/pbg/years - Lista de años disponibles
app.get('/api/pbg/years', async (req, res) => {
    try {
        const years = await pbgService.getYears();
        sendResponse(res, 'Lista de años obtenida exitosamente', years);
    } catch (error) {
        handleError(res, error, 'Error al obtener lista de años');
    }
});

// GET /api/pbg/sectors - Lista de sectores disponibles
app.get('/api/pbg/sectors', async (req, res) => {
    try {
        const sectors = await pbgService.getSectors();
        sendResponse(res, 'Lista de sectores obtenida exitosamente', sectors);
    } catch (error) {
        handleError(res, error, 'Error al obtener lista de sectores');
    }
});

// GET /api/pbg/sector/{codigo} - Datos de un sector específico
app.get('/api/pbg/sector/:codigo', async (req, res) => {
    try {
        const { codigo } = req.params;
        
        if (!codigo) {
            return sendResponse(res, 'Código de sector es requerido', null, 400);
        }

        const sectorData = await pbgService.getBySector(codigo);
        
        if (sectorData.length === 0) {
            return sendResponse(res, 'No se encontraron datos para el sector especificado', [], 404);
        }

        sendResponse(res, `Datos del sector ${codigo} obtenidos exitosamente`, sectorData);
    } catch (error) {
        handleError(res, error, 'Error al obtener datos del sector');
    }
});

// Ruta para testing de conexión DB
app.get('/api/health', async (req, res) => {
    try {
        const db = require('../config/database');
        await db.query('SELECT 1');
        sendResponse(res, 'API y base de datos funcionando correctamente', {
            status: 'healthy',
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        handleError(res, error, 'Error de conexión a la base de datos');
    }
});

// Manejo de rutas no encontradas
app.use('*', (req, res) => {
    sendResponse(res, 'Endpoint no encontrado', null, 404);
});

// Para desarrollo local
if (process.env.NODE_ENV !== 'production') {
    const PORT = process.env.PORT || 3000;
    app.listen(PORT, () => {
        console.log(`Servidor corriendo en http://localhost:${PORT}`);
    });
}

// Export para Vercel
module.exports = app;
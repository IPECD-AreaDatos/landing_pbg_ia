const db = require('../config/database');

class PbgService {
    
    // Obtener datos para gráficos del dashboard
    async getChartData() {
        try {
            // Obtener el último año disponible
            const [latestYearResult] = await db.query('SELECT MAX(año) as max_year FROM pbg_anual_desglosado');
            const latestYear = latestYearResult.max_year;
            
            // Datos del último año (sectores principales)
            const latestData = await db.query(`
                SELECT letra, descripcion, valor, variacion_interanual 
                FROM pbg_anual_desglosado 
                WHERE año = ? AND LENGTH(letra) = 1 AND letra != 'PBG'
                ORDER BY letra
            `, [latestYear]);

            // Total PBG del último año
            const [pbgTotal] = await db.query(`
                SELECT valor FROM pbg_anual_desglosado 
                WHERE año = ? AND letra = 'PBG'
            `, [latestYear]);

            // Evolución histórica del PBG (últimos 10 años)
            const evolution = await db.query(`
                SELECT año, valor 
                FROM pbg_anual_desglosado 
                WHERE letra = 'PBG' AND año >= ?
                ORDER BY año
            `, [latestYear - 9]);

            // Top 5 sectores por valor
            const topSectors = await db.query(`
                SELECT letra, descripcion, valor 
                FROM pbg_anual_desglosado 
                WHERE año = ? AND LENGTH(letra) = 1 AND letra != 'PBG'
                ORDER BY valor DESC 
                LIMIT 5
            `, [latestYear]);

            return {
                year: latestYear,
                total_pbg: pbgTotal ? pbgTotal.valor : 0,
                sectors: latestData,
                evolution: evolution,
                top_sectors: topSectors
            };
        } catch (error) {
            console.error('Error in getChartData:', error);
            throw error;
        }
    }

    // Obtener datos del último año
    async getLatestData() {
        try {
            const [latestYearResult] = await db.query('SELECT MAX(año) as max_year FROM pbg_anual_desglosado');
            const latestYear = latestYearResult.max_year;

            const data = await db.query(`
                SELECT letra, descripcion, año, valor, variacion_interanual 
                FROM pbg_anual_desglosado 
                WHERE año = ? AND LENGTH(letra) = 1
                ORDER BY letra
            `, [latestYear]);

            return {
                year: latestYear,
                data: data
            };
        } catch (error) {
            console.error('Error in getLatestData:', error);
            throw error;
        }
    }

    // Obtener lista de años disponibles
    async getYears() {
        try {
            const years = await db.query(`
                SELECT DISTINCT año 
                FROM pbg_anual_desglosado 
                ORDER BY año DESC
            `);
            
            return years.map(row => row.año);
        } catch (error) {
            console.error('Error in getYears:', error);
            throw error;
        }
    }

    // Obtener lista de sectores
    async getSectors() {
        try {
            const sectors = await db.query(`
                SELECT DISTINCT letra, descripcion 
                FROM pbg_anual_desglosado 
                WHERE LENGTH(letra) = 1
                ORDER BY letra
            `);
            
            return sectors;
        } catch (error) {
            console.error('Error in getSectors:', error);
            throw error;
        }
    }

    // Obtener datos por sector específico
    async getBySector(sectorCode) {
        try {
            let query = '';
            let params = [];

            if (sectorCode.length === 1) {
                // Sector principal - incluir todos los subsectores
                query = `
                    SELECT letra, descripcion, año, valor, variacion_interanual 
                    FROM pbg_anual_desglosado 
                    WHERE letra LIKE ?
                    ORDER BY año DESC, letra
                `;
                params = [sectorCode + '%'];
            } else {
                // Subsector específico
                query = `
                    SELECT letra, descripcion, año, valor, variacion_interanual 
                    FROM pbg_anual_desglosado 
                    WHERE letra = ?
                    ORDER BY año DESC
                `;
                params = [sectorCode];
            }

            const data = await db.query(query, params);
            return data;
        } catch (error) {
            console.error('Error in getBySector:', error);
            throw error;
        }
    }
}

module.exports = new PbgService();
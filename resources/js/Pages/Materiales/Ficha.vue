<template>
    <Head :title="material.titulo" />

    <div class="ficha-page">
        <div class="ficha-card">

            <!-- Cabecera -->
            <div class="ficha-header">
                <div class="ficha-codigo-badge">{{ material.codigo }}</div>
                <h1 class="ficha-titulo">{{ material.titulo }}</h1>
                <p v-if="material.autor" class="ficha-autor">{{ material.autor }}</p>
            </div>

            <!-- Ubicación física destacada -->
            <div v-if="material.clasificacion_fisica" class="ficha-ubicacion">
                <div class="ficha-ubicacion-label">
                    <i class="bi bi-geo-alt-fill"></i> Ubicación física
                </div>
                <div class="ficha-ubicacion-codigo">{{ material.clasificacion_fisica }}</div>
                <div class="ficha-ubicacion-desglose" v-if="desglose">
                    <span v-for="(item, i) in desglose" :key="i" class="ficha-tag">
                        <strong>{{ item.label }}:</strong> {{ item.valor }}
                    </span>
                </div>
            </div>

            <!-- Datos del material -->
            <div class="ficha-datos">
                <div v-if="material.area" class="ficha-dato">
                    <span class="ficha-dato-label">Clasificación Dewey</span>
                    <span class="ficha-dato-valor">
                        <code>{{ material.area.codigo_dewey }}</code> — {{ material.area.nombre }}
                    </span>
                </div>
                <div v-if="material.categoria" class="ficha-dato">
                    <span class="ficha-dato-label">Tipo de material</span>
                    <span class="ficha-dato-valor">{{ material.categoria }}</span>
                </div>
                <div v-if="material.editorial" class="ficha-dato">
                    <span class="ficha-dato-label">Editorial</span>
                    <span class="ficha-dato-valor">{{ material.editorial }}</span>
                </div>
                <div v-if="material.anio_publicacion" class="ficha-dato">
                    <span class="ficha-dato-label">Año de publicación</span>
                    <span class="ficha-dato-valor">{{ material.anio_publicacion }}</span>
                </div>
                <div class="ficha-dato">
                    <span class="ficha-dato-label">Disponibilidad</span>
                    <span class="ficha-dato-valor">
                        <span :class="material.disponibilidad > 0 ? 'badge badge-success' : 'badge badge-secondary'">
                            {{ material.disponibilidad > 0 ? `${material.disponibilidad} disponible(s)` : 'No disponible' }}
                        </span>
                    </span>
                </div>
            </div>

            <div class="ficha-footer">
                <i class="bi bi-book"></i> e-LibeR — Sistema de Gestión de Biblioteca
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    material: { type: Object, required: true },
})

// Desglosa BOTAN-A-(M)3-5 en partes legibles
const desglose = computed(() => {
    const cf = props.material.clasificacion_fisica
    if (!cf) return null

    // Formato: {ABREV}-{PASILLO}-({TIPO}){ESTANTE}-{NIVEL}
    const match = cf.match(/^([^-]+)-([^-]+)-\(([^)]+)\)(\d+)-(\d+)$/)
    if (!match) return null

    const [, abrev, pasillo, tipo, estante, nivel] = match
    return [
        { label: 'Área',    valor: abrev },
        { label: 'Pasillo', valor: pasillo },
        { label: 'Mueble',  valor: tipo === 'E' ? 'Estante' : 'Mueble' },
        { label: 'Número',  valor: estante },
        { label: 'Nivel',   valor: nivel },
    ]
})
</script>

<style>
*, *::before, *::after { box-sizing: border-box; }

body { margin: 0; background: #f0f4f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

.ficha-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
}

.ficha-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    max-width: 480px;
    width: 100%;
    overflow: hidden;
}

.ficha-header {
    background: linear-gradient(135deg, #1b5e20, #388e3c);
    color: #fff;
    padding: 1.5rem 1.5rem 1.2rem;
    text-align: center;
}

.ficha-codigo-badge {
    display: inline-block;
    background: rgba(255,255,255,.2);
    border: 1px solid rgba(255,255,255,.4);
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .08em;
    padding: .2rem .8rem;
    margin-bottom: .6rem;
    font-family: monospace;
}

.ficha-titulo {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0 0 .3rem;
    line-height: 1.3;
}

.ficha-autor {
    opacity: .85;
    font-size: .9rem;
    margin: 0;
}

.ficha-ubicacion {
    background: #fff8e1;
    border-bottom: 1px solid #ffe082;
    padding: 1rem 1.5rem;
    text-align: center;
}

.ficha-ubicacion-label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #f57f17;
    margin-bottom: .3rem;
}

.ficha-ubicacion-codigo {
    font-family: 'Courier New', monospace;
    font-size: 1.5rem;
    font-weight: 700;
    color: #e65100;
    letter-spacing: .04em;
}

.ficha-ubicacion-desglose {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: .4rem;
    margin-top: .6rem;
}

.ficha-tag {
    background: #fff3e0;
    border: 1px solid #ffcc80;
    border-radius: 6px;
    font-size: .72rem;
    padding: .15rem .5rem;
    color: #bf360c;
}

.ficha-datos {
    padding: .8rem 1.5rem 1rem;
}

.ficha-dato {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: .5rem;
    padding: .5rem 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: .875rem;
}

.ficha-dato:last-child { border-bottom: none; }

.ficha-dato-label {
    color: #888;
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
    white-space: nowrap;
    flex-shrink: 0;
}

.ficha-dato-valor {
    color: #222;
    text-align: right;
}

.badge-success { background: #2e7d32; color: #fff; padding: .2rem .6rem; border-radius: 12px; font-size: .75rem; }
.badge-secondary { background: #9e9e9e; color: #fff; padding: .2rem .6rem; border-radius: 12px; font-size: .75rem; }

.ficha-footer {
    background: #f5f5f5;
    text-align: center;
    font-size: .72rem;
    color: #aaa;
    padding: .6rem;
}
</style>

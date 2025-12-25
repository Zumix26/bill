<script setup lang="ts">
import { computed, onBeforeMount, ref, watch } from 'vue';
import axios from '@/utils/axios';
import { useTheme } from 'vuetify';

const theme = useTheme();

interface Stock {
    secid: string;
    shortname: string;
    name: string;
}

interface HistoryPoint {
    x: number;
    y: number[];
    volume: number;
    date: string;
}

interface TradeSignal {
    date: number;
    type: 'buy' | 'sell';
    price: number;
    reason: string;
    confidence: 'high' | 'medium' | 'low';
    timeframe?: string;
}

interface MarketAnalysis {
    currentSituation: string;
    trend: 'bullish' | 'bearish' | 'sideways';
    alligatorState: 'awakening' | 'sleeping' | 'eating';
    potentialEntryPoints: TradeSignal[];
    riskLevel: 'low' | 'medium' | 'high';
    recommendations: string[];
}

const baseURL = import.meta.env.VITE_SERVER_URL || 'http://localhost:8000/api/';
const token = localStorage.getItem('token') || '';

const loading = ref(true);
const loadingStocks = ref(true);
const stocks = ref<Stock[]>([]);
const selectedSecid = ref<string>('');
const stockInfo = ref<Stock | null>(null);
const historyData = ref<HistoryPoint[]>([]);
const days = ref(90);

const alligatorData = ref<any[]>([]);
const aoData = ref<any[]>([]);
const reversalBars = ref<any[]>([]);
const tradeSignals = ref<TradeSignal[]>([]);
const marketAnalysis = ref<MarketAnalysis | null>(null);

onBeforeMount(async () => {
    await loadStocks();
});

async function loadStocks() {
    loadingStocks.value = true;
    try {
        const response = await axios.get(`${baseURL}moex/stocks`, {
            headers: { Authorization: `Bearer ${token}` }
        });
        stocks.value = response.data?.data || response.data || [];
        stocks.value.sort((a, b) => a.shortname.localeCompare(b.shortname));
        
        if (stocks.value.length > 0 && !selectedSecid.value) {
            selectedSecid.value = stocks.value[0].secid;
        }
    } catch (error: any) {
        console.error('Error loading stocks:', error);
    } finally {
        loadingStocks.value = false;
    }
}

async function loadData() {
    if (!selectedSecid.value) {
        historyData.value = [];
        stockInfo.value = null;
        return;
    }
    
    loading.value = true;
    try {
        const stock = stocks.value.find(s => s.secid === selectedSecid.value);
        if (stock) {
            stockInfo.value = stock;
        }
        
        const historyResponse = await axios.get(`${baseURL}moex/stocks/${selectedSecid.value}/history?days=${days.value}`, {
            headers: { Authorization: `Bearer ${token}` }
        });
        
        historyData.value = historyResponse.data?.data || [];
        
        const indicatorsResponse = await axios.get(`${baseURL}moex/stocks/${selectedSecid.value}/indicators?days=${days.value}`, {
            headers: { Authorization: `Bearer ${token}` }
        });
        
        if (indicatorsResponse.data?.success && indicatorsResponse.data?.data) {
            const indicators = indicatorsResponse.data.data;
            alligatorData.value = indicators.alligator || [];
            aoData.value = indicators.ao || [];
            reversalBars.value = indicators.reversalBars || [];
            tradeSignals.value = indicators.tradeSignals || [];
            marketAnalysis.value = indicators.marketAnalysis || null;
        }
    } catch (error: any) {
        console.error('Error loading data:', error);
    } finally {
        loading.value = false;
    }
}

watch(selectedSecid, () => {
    if (selectedSecid.value) {
        loadData();
    }
});

watch(days, () => {
    if (selectedSecid.value) {
        loadData();
    }
});

function formatPrice(price: number): string {
    return price.toLocaleString('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(timestamp: number): string {
    const date = new Date(timestamp);
    return date.toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

const todayReversalBar = computed(() => {
    if (historyData.value.length === 0) return null;
    const lastBarIndex = historyData.value.length - 1;
    return reversalBars.value.find(rb => rb.index === lastBarIndex) || null;
});

const alligatorChartOptions = computed(() => {
    return {
        chart: {
            type: 'line',
            height: 600,
            fontFamily: 'inherit',
            foreColor: '#666',
            toolbar: { show: true },
            zoom: { enabled: true }
        },
        stroke: {
            width: [2, 2, 2, 2],
            curve: ['smooth', 'smooth', 'smooth', 'smooth']
        },
        colors: ['#333', '#e74c3c', '#3498db', '#2ecc71'],
        xaxis: {
            type: 'datetime',
            labels: {
                datetimeUTC: false,
                format: 'dd.MM.yyyy',
                style: {
                    colors: '#666'
                }
            }
        },
        yaxis: {
            labels: {
                formatter: (val: number) => formatPrice(val) + ' ₽',
                style: {
                    colors: '#666'
                }
            }
        },
        tooltip: {
            theme: 'light',
            x: { format: 'dd.MM.yyyy HH:mm' },
            shared: true
        },
        legend: {
            show: true,
            position: 'top',
            fontSize: '13px',
            fontFamily: 'inherit',
            labels: {
                colors: '#666'
            }
        },
        grid: {
            borderColor: '#e0e0e0',
            strokeDashArray: 3
        }
    };
});

const alligatorChartSeries = computed(() => {
    const closeData = historyData.value.map(h => ({ x: h.x, y: h.y[3] }));
    
    const jawData = alligatorData.value
        .filter(d => d.jaw !== null)
        .map(d => ({ x: d.x, y: d.jaw }));
    const teethData = alligatorData.value
        .filter(d => d.teeth !== null)
        .map(d => ({ x: d.x, y: d.teeth }));
    const lipsData = alligatorData.value
        .filter(d => d.lips !== null)
        .map(d => ({ x: d.x, y: d.lips }));
    
    return [
        { name: 'Цена', data: closeData },
        { name: 'Челюсть (13)', data: jawData },
        { name: 'Зубы (8)', data: teethData },
        { name: 'Губы (5)', data: lipsData }
    ];
});

const aoChartOptions = computed(() => {
    return {
        chart: {
            type: 'bar',
            height: 200,
            fontFamily: 'inherit',
            foreColor: '#666'
        },
        plotOptions: {
            bar: {
                columnWidth: '80%',
                colors: {
                    ranges: [{
                        from: -Infinity,
                        to: 0,
                        color: '#e74c3c'
                    }, {
                        from: 0,
                        to: Infinity,
                        color: '#2ecc71'
                    }]
                }
            }
        },
        xaxis: {
            type: 'datetime',
            labels: {
                datetimeUTC: false,
                format: 'dd.MM.yyyy',
                style: {
                    colors: '#666'
                }
            }
        },
        yaxis: {
            labels: {
                formatter: (val: number) => val.toFixed(2),
                style: {
                    colors: '#666'
                }
            }
        },
        tooltip: {
            theme: 'light'
        },
        grid: {
            borderColor: '#e0e0e0',
            strokeDashArray: 3
        }
    };
});

const aoChartSeries = computed(() => {
    return [{
        name: 'AO',
        data: aoData.value
            .filter(d => d.value !== null)
            .map(d => ({ x: d.x, y: d.value }))
    }];
});
</script>

<template>
    <div class="indicators-page">
        <v-container fluid class="pa-6">
            <v-row>
                <v-col cols="12">
                    <div class="page-header mb-6">
                        <div class="d-flex align-center mb-2">
                            <v-icon icon="mdi-chart-timeline-variant" size="40" color="primary" class="mr-3"></v-icon>
                            <div>
                                <h1 class="text-h4 font-weight-medium mb-1">Индикаторы Билла Вильямса</h1>
                                <p class="text-body-2 text-grey-darken-1">Анализ акций MOEX</p>
                            </div>
                        </div>
                    </div>
                    
                    <v-card class="control-card mb-6" elevation="1">
                        <v-card-text class="pa-5">
                            <v-row align="center" dense>
                                <v-col cols="12" md="5">
                                    <v-select
                                        v-model="selectedSecid"
                                        :items="stocks"
                                        item-title="shortname"
                                        item-value="secid"
                                        label="Акция"
                                        variant="outlined"
                                        density="comfortable"
                                        prepend-inner-icon="mdi-chart-line"
                                        :loading="loadingStocks"
                                        :disabled="loadingStocks"
                                        hide-details
                                    >
                                        <template v-slot:item="{ props, item }">
                                            <v-list-item v-bind="props">
                                                <template v-slot:prepend>
                                                    <v-avatar size="28" color="primary" variant="tonal">
                                                        <v-icon size="16">mdi-trending-up</v-icon>
                                                    </v-avatar>
                                                </template>
                                                <template v-slot:title>
                                                    <div class="text-body-2 font-weight-medium">{{ item.raw.shortname }}</div>
                                                    <div class="text-caption text-grey">{{ item.raw.secid }}</div>
                                                </template>
                                            </v-list-item>
                                        </template>
                                        <template v-slot:selection="{ item }">
                                            <div class="d-flex align-center">
                                                <v-icon icon="mdi-trending-up" size="18" color="primary" class="mr-2"></v-icon>
                                                <span class="text-body-2 font-weight-medium">{{ item.raw.shortname }}</span>
                                            </div>
                                        </template>
                                    </v-select>
                                </v-col>
                                <v-col cols="12" md="3">
                                    <v-select
                                        v-model="days"
                                        :items="[30, 60, 90, 180, 365]"
                                        label="Период"
                                        variant="outlined"
                                        density="comfortable"
                                        prepend-inner-icon="mdi-calendar-range"
                                        hide-details
                                    ></v-select>
                                </v-col>
                                <v-col cols="12" md="2" v-if="stockInfo">
                                    <v-card variant="tonal" color="primary" class="pa-3">
                                        <div class="text-caption text-medium-emphasis mb-1">Тикер</div>
                                        <div class="text-body-1 font-weight-bold">{{ stockInfo.secid }}</div>
                                    </v-card>
                                </v-col>
                                <v-col cols="12" md="2" class="text-right">
                                    <v-btn
                                        color="primary"
                                        @click="loadData"
                                        variant="flat"
                                        size="large"
                                        :disabled="!selectedSecid || loading"
                                        :loading="loading"
                                        class="refresh-btn"
                                    >
                                        <v-icon icon="mdi-refresh" class="mr-2"></v-icon>
                                        Обновить
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </v-card-text>
                    </v-card>
                
                <v-alert
                    v-if="!selectedSecid"
                    type="info"
                    variant="text"
                    density="compact"
                    class="mb-4"
                >
                    Выберите акцию для анализа
                </v-alert>
                
                <template v-if="selectedSecid">
                    <v-row v-if="loading">
                        <v-col cols="12" class="text-center py-8">
                            <v-progress-circular 
                                indeterminate 
                                color="primary"
                                size="48"
                            ></v-progress-circular>
                            <div class="mt-4 text-body-2 text-grey">Загрузка данных...</div>
                        </v-col>
                    </v-row>
                    
                    <template v-else-if="historyData.length > 0">
                        <v-card class="analysis-card mb-6" elevation="1" v-if="marketAnalysis">
                            <v-card-title class="card-title pa-5 pb-3">
                                <v-icon icon="mdi-chart-box" size="22" color="primary" class="mr-2"></v-icon>
                                <span class="text-h6 font-weight-medium">Анализ рынка</span>
                            </v-card-title>
                            <v-card-text class="pa-5 pt-3">
                                <v-row dense>
                                    <v-col cols="12" md="4">
                                        <v-card variant="tonal" :color="marketAnalysis.trend === 'bullish' ? 'success' : marketAnalysis.trend === 'bearish' ? 'error' : 'warning'" class="stat-box pa-4">
                                            <div class="d-flex align-center mb-2">
                                                <v-icon 
                                                    :icon="marketAnalysis.trend === 'bullish' ? 'mdi-trending-up' : marketAnalysis.trend === 'bearish' ? 'mdi-trending-down' : 'mdi-trending-neutral'"
                                                    size="24"
                                                    class="mr-2"
                                                ></v-icon>
                                                <div class="text-caption text-medium-emphasis">Тренд</div>
                                            </div>
                                            <div class="text-h6 font-weight-bold">
                                                {{ marketAnalysis.trend === 'bullish' ? 'Бычий' : marketAnalysis.trend === 'bearish' ? 'Медвежий' : 'Боковой' }}
                                            </div>
                                        </v-card>
                                    </v-col>
                                    <v-col cols="12" md="4">
                                        <v-card variant="tonal" color="primary" class="stat-box pa-4">
                                            <div class="d-flex align-center mb-2">
                                                <v-icon icon="mdi-alligator" size="24" class="mr-2"></v-icon>
                                                <div class="text-caption text-medium-emphasis">Alligator</div>
                                            </div>
                                            <div class="text-h6 font-weight-bold">
                                                {{ marketAnalysis.alligatorState === 'awakening' ? 'Пробуждение' : marketAnalysis.alligatorState === 'sleeping' ? 'Сон' : 'Ест' }}
                                            </div>
                                        </v-card>
                                    </v-col>
                                    <v-col cols="12" md="4">
                                        <v-card variant="tonal" :color="marketAnalysis.riskLevel === 'low' ? 'success' : marketAnalysis.riskLevel === 'medium' ? 'warning' : 'error'" class="stat-box pa-4">
                                            <div class="d-flex align-center mb-2">
                                                <v-icon 
                                                    :icon="marketAnalysis.riskLevel === 'low' ? 'mdi-shield-check' : marketAnalysis.riskLevel === 'medium' ? 'mdi-shield-alert' : 'mdi-shield-remove'"
                                                    size="24"
                                                    class="mr-2"
                                                ></v-icon>
                                                <div class="text-caption text-medium-emphasis">Риск</div>
                                            </div>
                                            <div class="text-h6 font-weight-bold">
                                                {{ marketAnalysis.riskLevel === 'low' ? 'Низкий' : marketAnalysis.riskLevel === 'medium' ? 'Средний' : 'Высокий' }}
                                            </div>
                                        </v-card>
                                    </v-col>
                                </v-row>
                                
                                <v-divider class="my-4"></v-divider>
                                
                                <div class="mb-4" v-if="marketAnalysis.currentSituation && marketAnalysis.currentSituation.trim() !== ''">
                                    <div class="d-flex align-center mb-3">
                                        <v-icon icon="mdi-information" size="20" color="primary" class="mr-2"></v-icon>
                                        <div class="text-subtitle-2 font-weight-medium">Текущая ситуация</div>
                                    </div>
                                    <div class="text-body-2 situation-text" style="white-space: pre-line; line-height: 1.7;">
                                        {{ marketAnalysis.currentSituation }}
                                    </div>
                                </div>
                                
                                <div v-if="marketAnalysis.recommendations && marketAnalysis.recommendations.length > 0">
                                    <div class="d-flex align-center mb-3">
                                        <v-icon icon="mdi-lightbulb-on" size="20" color="success" class="mr-2"></v-icon>
                                        <div class="text-subtitle-2 font-weight-medium">Рекомендации</div>
                                    </div>
                                    <v-list class="pa-0">
                                        <v-list-item
                                            v-for="(rec, index) in marketAnalysis.recommendations"
                                            :key="index"
                                            class="px-0 mb-2"
                                        >
                                            <template v-slot:prepend>
                                                <v-icon icon="mdi-check-circle" size="18" color="success" class="mr-3"></v-icon>
                                            </template>
                                            <v-list-item-title class="text-body-2">{{ rec }}</v-list-item-title>
                                        </v-list-item>
                                    </v-list>
                                </div>
                            </v-card-text>
                        </v-card>
                        
                        <v-card class="chart-card mb-6" elevation="1">
                            <v-card-title class="card-title pa-5 pb-3">
                                <v-icon icon="mdi-chart-line" size="22" color="primary" class="mr-2"></v-icon>
                                <span class="text-h6 font-weight-medium">График цены и Alligator</span>
                            </v-card-title>
                            <v-card-text class="pa-5 pt-3">
                                <div class="legend mb-4">
                                    <v-chip size="small" variant="tonal" color="grey" class="mr-2">Цена</v-chip>
                                    <v-chip size="small" variant="tonal" color="error" class="mr-2">Челюсть (13)</v-chip>
                                    <v-chip size="small" variant="tonal" color="primary" class="mr-2">Зубы (8)</v-chip>
                                    <v-chip size="small" variant="tonal" color="success">Губы (5)</v-chip>
                                </div>
                                <apexchart
                                    type="line"
                                    height="600"
                                    :options="alligatorChartOptions"
                                    :series="alligatorChartSeries"
                                ></apexchart>
                            </v-card-text>
                        </v-card>
                        
                        <v-card class="chart-card mb-6" elevation="1">
                            <v-card-title class="card-title pa-5 pb-3">
                                <v-icon icon="mdi-chart-bar" size="22" color="primary" class="mr-2"></v-icon>
                                <span class="text-h6 font-weight-medium">Awesome Oscillator</span>
                            </v-card-title>
                            <v-card-text class="pa-5 pt-3">
                                <div class="text-body-2 text-grey mb-4">
                                    Разница между 5-периодной и 34-периодной SMMA медианной цены
                                </div>
                                <apexchart
                                    type="bar"
                                    height="200"
                                    :options="aoChartOptions"
                                    :series="aoChartSeries"
                                ></apexchart>
                            </v-card-text>
                        </v-card>
                        
                        <v-card class="reversal-card mb-6" elevation="1" v-if="todayReversalBar">
                            <v-card-title class="card-title pa-5 pb-3">
                                <v-icon icon="mdi-chart-timeline-variant-shimmer" size="22" color="primary" class="mr-2"></v-icon>
                                <span class="text-h6 font-weight-medium">Разворотный бар</span>
                            </v-card-title>
                            <v-card-text class="pa-5 pt-3">
                                <v-row dense>
                                    <v-col cols="6" md="3">
                                        <div class="text-caption text-grey mb-2">Дата</div>
                                        <div class="text-body-1 font-weight-medium">{{ formatDate(todayReversalBar.x) }}</div>
                                    </v-col>
                                    <v-col cols="6" md="3">
                                        <div class="text-caption text-grey mb-2">Паттерн</div>
                                        <div class="text-body-1 font-weight-medium">{{ todayReversalBar.pattern }}</div>
                                    </v-col>
                                    <v-col cols="6" md="2">
                                        <div class="text-caption text-grey mb-2">Тип</div>
                                        <v-chip
                                            :color="todayReversalBar.type === 'buy' ? 'success' : 'error'"
                                            size="small"
                                            variant="flat"
                                        >
                                            {{ todayReversalBar.type === 'buy' ? 'Покупка' : 'Продажа' }}
                                        </v-chip>
                                    </v-col>
                                    <v-col cols="6" md="2">
                                        <div class="text-caption text-grey mb-2">Уверенность</div>
                                        <v-chip
                                            :color="todayReversalBar.confidence === 'high' ? 'success' : todayReversalBar.confidence === 'medium' ? 'warning' : 'error'"
                                            size="small"
                                            variant="flat"
                                        >
                                            {{ todayReversalBar.confidence === 'high' ? 'Высокая' : todayReversalBar.confidence === 'medium' ? 'Средняя' : 'Низкая' }}
                                        </v-chip>
                                    </v-col>
                                    <v-col cols="12" md="2">
                                        <div class="text-caption text-grey mb-2">Цена</div>
                                        <div class="text-h6 font-weight-bold text-primary">{{ formatPrice(todayReversalBar.price) }} ₽</div>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </template>
                    
                    <v-alert
                        v-else
                        type="warning"
                        variant="text"
                        density="compact"
                        class="mb-4"
                    >
                        Исторические данные недоступны
                    </v-alert>
                </template>
            </v-col>
        </v-row>
    </v-container>
    </div>
</template>

<style scoped>
.indicators-page {
    background: #fafafa;
    min-height: 100vh;
}

.page-header {
    padding: 20px 0;
}

.control-card {
    border-radius: 12px;
    border: 1px solid rgba(0, 0, 0, 0.08);
}

.analysis-card,
.chart-card,
.reversal-card {
    border-radius: 12px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
}

.analysis-card:hover,
.chart-card:hover,
.reversal-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}

.card-title {
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    background: rgba(93, 135, 255, 0.02);
}

.stat-box {
    border-radius: 8px;
    transition: all 0.2s ease;
}

.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
}

.situation-text {
    background: rgba(93, 135, 255, 0.03);
    padding: 16px;
    border-radius: 8px;
    border-left: 3px solid rgb(var(--v-theme-primary));
}

.legend {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.refresh-btn {
    transition: all 0.3s ease;
    text-transform: none;
    letter-spacing: normal;
    font-weight: 500;
}

.refresh-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(93, 135, 255, 0.3) !important;
}

.refresh-btn :deep(.v-icon) {
    transition: transform 0.3s ease;
}

.refresh-btn:hover:not(:disabled) :deep(.v-icon) {
    transform: rotate(180deg);
}

:deep(.v-card) {
    border-radius: 12px;
}

:deep(.v-select) {
    font-size: 14px;
}

:deep(.v-field) {
    font-size: 14px;
    border-radius: 8px;
}

:deep(.v-btn) {
    border-radius: 8px;
}
</style>

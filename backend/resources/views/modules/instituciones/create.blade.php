{--
 * ============================================================
 * Vista Blade — Formulario de creacion de institucion
 * Modulo: Instituciones
 * MPL-OMEGA-05 §7.1
 * @version 1.0.0
 * ============================================================
--}
@extends('layouts.app')
@section('title', 'Nueva Institución')
@section('content')

<div class="mb-6">
    <a href="{{ route('ca.instituciones.index') }}"
       class="inline-flex items-center gap-2 text-sm font-body text-omg-kashmir hover:text-omg-nile mb-3">
        <i class="fa-solid fa-arrow-left text-xs"></i> Volver a mis instituciones
    </a>
    <h1 class="text-2xl font-heading font-semibold text-omg-nile">Nueva Institución</h1>
    <p class="text-sm font-body text-omg-kashmir mt-1">Completa los tres pasos para configurar tu institución</p>
</div>

<div x-data="{
    paso: 1,
    nombre: '{{ old('nombre') }}',
    logo: '{{ old('logo') }}',
    rubros: [
        { nombre: 'Ordinario', porcentaje: 80 },
        { nombre: 'Extraordinario', porcentaje: 60 }
    ],
    periodos: [],
    periodoFechaInicio: '',
    periodoFechaFin: '',
    mostrarPersonalizado: false,

    agregarRubro() {
        this.rubros.push({ nombre: '', porcentaje: 70 });
    },
    eliminarRubro(i) {
        if (this.rubros.length > 1) this.rubros.splice(i, 1);
    },
    agregarPeriodo(nombre) {
        const n = nombre.trim();
        if (n && !this.periodos.map(p => p.toLowerCase()).includes(n.toLowerCase())) {
            this.periodos.push(n);
        }
    },
    eliminarPeriodo(i) {
        this.periodos.splice(i, 1);
    },
    get periodosOpciones() {
        const anio = new Date().getFullYear();
        return [
            'Ene-Jun ' + anio,
            'Ago-Dic ' + anio,
            'Ene-Jun ' + (anio + 1),
            'Ago-Dic ' + (anio + 1),
        ];
    },
    get periodoNombreGenerado() {
        if (!this.periodoFechaInicio || !this.periodoFechaFin) return '';
        const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                       'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        const [anioI, mesI] = this.periodoFechaInicio.split('-').map(Number);
        const [anioF, mesF] = this.periodoFechaFin.split('-').map(Number);
        const nomI = meses[mesI - 1];
        const nomF = meses[mesF - 1];
        return anioI === anioF
            ? `${nomI} - ${nomF} ${anioI}`
            : `${nomI} ${anioI} - ${nomF} ${anioF}`;
    },
    get periodoFechasValidas() {
        return this.periodoFechaInicio !== '' && this.periodoFechaFin !== ''
            && this.periodoFechaFin >= this.periodoFechaInicio;
    },
    paso1Valido() {
        return this.nombre.trim().length >= 3 && this.logo.trim().length >= 20;
    },
    paso2Valido() {
        return this.rubros.every(r => r.nombre.trim() !== '' && r.porcentaje >= 1 && r.porcentaje <= 100);
    },
    paso3Valido() {
        return this.periodos.length >= 1;
    }
}">

    {{-- Indicador de pasos --}}
    <div class="flex items-center gap-2 mb-6 max-w-lg">
        @foreach ([1 => 'Datos', 2 => 'Rubros', 3 => 'Periodos'] as $n => $label)
            <div class="flex items-center gap-2 flex-1">
                <div class="flex items-center gap-2"
                     :class="paso >= {{ $n }} ? 'opacity-100' : 'opacity-40'">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-heading font-bold transition-colors"
                         :class="paso > {{ $n }} ? 'bg-green-500 text-white' : (paso === {{ $n }} ? 'bg-omg-coral text-white' : 'bg-omg-chardon text-omg-kashmir')">
                        <span x-show="paso <= {{ $n }}">{{ $n }}</span>
                        <i x-show="paso > {{ $n }}" class="fa-solid fa-check text-xs"></i>
                    </div>
                    <span class="text-xs font-body text-omg-dark hidden sm:inline">{{ $label }}</span>
                </div>
                @if ($n < 3)
                    <div class="flex-1 h-0.5 mx-1 transition-colors"
                         :class="paso > {{ $n }} ? 'bg-green-400' : 'bg-omg-chardon'"></div>
                @endif
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('ca.instituciones.store') }}" id="form-institucion">
        @csrf

        {{-- ── PASO 1: Datos básicos ── --}}
        <div x-show="paso === 1" class="bg-white rounded-xl border border-omg-kashmir-dark p-6 max-w-lg">
            <h2 class="text-base font-heading font-semibold text-omg-nile mb-4">
                <i class="fa-solid fa-building-columns mr-2 text-omg-coral"></i>Datos de la institución
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-body text-omg-dark mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" x-model="nombre" required
                           placeholder="Ej: Tecnológico de Toluca"
                           class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile @error('nombre') border-red-400 @enderror"/>
                    @error('nombre') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-body text-omg-dark mb-1">URL del logotipo <span class="text-red-500">*</span></label>
                    <input type="text" name="logo" x-model="logo"
                           placeholder="https://ejemplo.com/logo.png"
                           class="w-full px-4 py-2.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"/>
                    <div x-show="logo.trim().length >= 20" class="mt-3 flex items-center gap-3">
                        <img :src="logo" alt="Vista previa"
                             class="w-16 h-16 object-contain rounded-lg border border-omg-kashmir-dark bg-omg-chardon p-1"
                        />
                        <p class="text-xs font-body text-omg-kashmir">Vista previa del logotipo</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <a href="{{ route('ca.instituciones.index') }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm hover:bg-omg-pastel transition-colors">
                    <i class="fa-solid fa-ban"></i> Cancelar
                </a>
                <button type="button" @click="if(paso1Valido()) paso = 2"
                        :class="paso1Valido() ? 'bg-omg-coral hover:bg-omg-coral-dark text-white' : 'bg-omg-chardon text-omg-kashmir cursor-not-allowed'"
                        class="flex items-center gap-2 px-5 py-2.5 font-heading font-semibold rounded-lg transition-colors text-sm">
                    Siguiente <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ── PASO 2: Rubros ── --}}
        <div x-show="paso === 2" class="bg-white rounded-xl border border-omg-kashmir-dark p-6 max-w-lg">
            <h2 class="text-base font-heading font-semibold text-omg-nile mb-1">
                <i class="fa-solid fa-percent mr-2 text-omg-coral"></i>Rubros de evaluación
            </h2>
            <p class="text-xs font-body text-omg-kashmir mb-4">Define los porcentajes mínimos de asistencia para cada rubro.</p>

            <div class="space-y-3">
                <template x-for="(rubro, i) in rubros" :key="i">
                    <div class="bg-omg-chardon rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-6 h-6 bg-omg-coral rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 x-text="i + 1"></div>
                            <input type="text" :name="'rubros[' + i + '][nombre]'" x-model="rubro.nombre"
                                   placeholder="Nombre del rubro (ej: Ordinario)"
                                   class="flex-1 px-3 py-1.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"/>
                            <button type="button" @click="eliminarRubro(i)"
                                    class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="text-xs font-body text-omg-kashmir w-28 flex-shrink-0">
                                Mínimo: <strong x-text="rubro.porcentaje + '%'"></strong>
                            </label>
                            <input type="range" :name="'rubros[' + i + '][porcentaje]'" x-model="rubro.porcentaje"
                                   min="1" max="100" class="flex-1 accent-omg-coral"/>
                            <input type="hidden" :name="'rubros[' + i + '][porcentaje]'" :value="rubro.porcentaje"/>
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" @click="agregarRubro()"
                    class="mt-3 flex items-center gap-2 px-3 py-1.5 border border-dashed border-omg-kashmir rounded-lg text-sm font-body text-omg-kashmir hover:border-omg-nile hover:text-omg-nile transition-colors">
                <i class="fa-solid fa-plus text-xs"></i> Agregar rubro
            </button>

            <div class="flex justify-between mt-6">
                <div class="flex items-center gap-2">
                    <button type="button" @click="paso = 1"
                            class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm hover:bg-omg-pastel transition-colors">
                        <i class="fa-solid fa-arrow-left"></i> Atrás
                    </button>
                    <a href="{{ route('ca.instituciones.index') }}"
                       class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm hover:bg-omg-pastel transition-colors">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </a>
                </div>
                <button type="button" @click="if(paso2Valido()) paso = 3"
                        :class="paso2Valido() ? 'bg-omg-coral hover:bg-omg-coral-dark text-white' : 'bg-omg-chardon text-omg-kashmir cursor-not-allowed'"
                        class="flex items-center gap-2 px-5 py-2.5 font-heading font-semibold rounded-lg transition-colors text-sm">
                    Siguiente <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- ── PASO 3: Periodos ── --}}
        <div x-show="paso === 3" class="bg-white rounded-xl border border-omg-kashmir-dark p-6 max-w-lg">
            <h2 class="text-base font-heading font-semibold text-omg-nile mb-1">
                <i class="fa-solid fa-calendar-alt mr-2 text-omg-coral"></i>Periodos académicos
            </h2>
            <p class="text-xs font-body text-omg-kashmir mb-4">Agrega al menos un periodo. Podrás agregar más después.</p>

            {{-- Opciones rápidas (selección única) --}}
            <div class="flex flex-wrap gap-2 mb-4">
                <template x-for="op in periodosOpciones" :key="op">
                    <button type="button"
                            @click="periodos.map(p=>p.toLowerCase()).includes(op.toLowerCase()) ? periodos = periodos.filter(p=>p.toLowerCase()!==op.toLowerCase()) : agregarPeriodo(op)"
                            :class="periodos.map(p=>p.toLowerCase()).includes(op.toLowerCase()) ? 'bg-omg-nile text-white border-omg-nile' : 'bg-white text-omg-nile border-omg-kashmir hover:border-omg-nile'"
                            class="px-3 py-1.5 border rounded-lg text-xs font-body transition-colors">
                        <i class="fa-solid fa-check mr-1" x-show="periodos.map(p=>p.toLowerCase()).includes(op.toLowerCase())"></i>
                        <span x-text="op"></span>
                    </button>
                </template>
            </div>

            {{-- Periodo personalizado con selector de fechas --}}
            <div class="mb-4">
                <button type="button" @click="mostrarPersonalizado = !mostrarPersonalizado"
                        class="text-xs font-body text-omg-nile hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-plus text-xs" :class="mostrarPersonalizado ? 'rotate-45' : ''"></i>
                    Agregar periodo personalizado
                </button>

                <div x-show="mostrarPersonalizado" x-transition class="mt-3 space-y-2 p-3 bg-omg-chardon rounded-xl border border-omg-kashmir-dark">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-body text-omg-dark mb-1">
                                Fecha de inicio <span class="text-red-500">*</span>
                            </label>
                            <input type="date" x-model="periodoFechaInicio"
                                   min="{{ now()->year }}-01-01"
                                   max="{{ now()->year + 1 }}-12-31"
                                   class="w-full px-3 py-1.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"/>
                        </div>
                        <div>
                            <label class="block text-xs font-body text-omg-dark mb-1">
                                Fecha de término <span class="text-red-500">*</span>
                            </label>
                            <input type="date" x-model="periodoFechaFin"
                                   :min="periodoFechaInicio || '{{ now()->year }}-01-01'"
                                   max="{{ now()->year + 1 }}-12-31"
                                   class="w-full px-3 py-1.5 bg-white border border-omg-kashmir rounded-lg text-sm font-body focus:outline-none focus:ring-2 focus:ring-omg-nile"/>
                        </div>
                    </div>

                    <div x-show="periodoNombreGenerado"
                         class="flex items-center gap-2 bg-white border border-omg-kashmir-dark rounded-lg px-3 py-1.5 text-xs font-body text-omg-dark">
                        <i class="fa-solid fa-calendar-check text-omg-nile text-xs"></i>
                        <span>Periodo: <strong x-text="periodoNombreGenerado" class="text-omg-nile"></strong></span>
                    </div>

                    <div class="flex justify-end">
                        <button type="button"
                                :disabled="!periodoFechasValidas"
                                @click="
                                    if (periodoFechasValidas && periodoNombreGenerado) {
                                        if (!periodos.map(p=>p.toLowerCase()).includes(periodoNombreGenerado.toLowerCase())) {
                                            agregarPeriodo(periodoNombreGenerado);
                                            periodoFechaInicio = ''; periodoFechaFin = '';
                                            mostrarPersonalizado = false;
                                        } else {
                                            alert('Este periodo ya fue agregado');
                                        }
                                    }
                                "
                                :class="periodoFechasValidas ? 'bg-omg-coral hover:bg-omg-coral-dark text-white' : 'bg-omg-chardon text-omg-kashmir cursor-not-allowed'"
                                class="px-3 py-1.5 rounded-lg text-xs font-body transition-colors">
                            Agregar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Periodos seleccionados --}}
            <div x-show="periodos.length > 0" class="space-y-2 mb-4">
                <p class="text-xs font-body text-omg-kashmir font-semibold">Periodos seleccionados:</p>
                <template x-for="(p, i) in periodos" :key="i">
                    <div class="flex items-center justify-between bg-omg-chardon rounded-lg px-3 py-2">
                        <span class="text-sm font-body text-omg-dark" x-text="p"></span>
                        <input type="hidden" :name="'periodos[]'" :value="p"/>
                        <button type="button" @click="eliminarPeriodo(i)"
                                class="text-red-400 hover:text-red-600 transition-colors">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>

            <div x-show="periodos.length === 0"
                 class="text-xs font-body text-orange-500 bg-orange-50 border border-orange-200 rounded-lg px-3 py-2 mb-4">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                Debes agregar al menos un periodo para continuar
            </div>

            <div class="flex justify-between mt-6">
                <div class="flex items-center gap-2">
                    <button type="button" @click="paso = 2"
                            class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm hover:bg-omg-pastel transition-colors">
                        <i class="fa-solid fa-arrow-left"></i> Atrás
                    </button>
                    <a href="{{ route('ca.instituciones.index') }}"
                       class="flex items-center gap-2 px-4 py-2.5 bg-omg-chardon text-omg-nile font-heading font-semibold rounded-lg text-sm hover:bg-omg-pastel transition-colors">
                        <i class="fa-solid fa-ban"></i> Cancelar
                    </a>
                </div>
                <button type="submit"
                        :disabled="!paso3Valido()"
                        :class="paso3Valido() ? 'bg-omg-coral hover:bg-omg-coral-dark text-white' : 'bg-omg-chardon text-omg-kashmir cursor-not-allowed'"
                        class="flex items-center gap-2 px-5 py-2.5 font-heading font-semibold rounded-lg transition-colors text-sm">
                    <i class="fa-solid fa-check"></i> Crear institución
                </button>
            </div>
        </div>

    </form>
</div>

@endsection

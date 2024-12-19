@extends('layout.master')

@section('content')

<style>
    #campoPeriodo{
        width: 50px;
        height: 30px;
        border: 0.2px rgb(207, 207, 207) solid;
        border-radius: 2px;
    }
    #campoAno{
        width: 60px;
        height: 30px;
        border: 0.2px rgb(207, 207, 207) solid;
        border-radius: 2px;
    }
</style>

<main>

    <h1>Reserva de salas</h1>

    <form action="" method="post">
            @csrf
            <div class="anoeperiodo">
                <label for="campoAno" class="form-label mt-3">Ano e período:</label><br>
                <select type="text" id="campoAno" name="campoAno">
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
                </select>

                <select type="number" id="campoPeriodo" name="campoPeriodo">
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>

            </div>
            
            <div class="nome">
                <div>                   
                    <label for="campoSala" class="form-label mt-3">Salas:</label>
                    <select class="form-select" aria-label="Default select example" id="campoSala" name="campoSala">
                        <option value="" disabled selected>Selecione</option>
                        @foreach ($salas as $sala)
                            <option value="{{ $sala->numero }}">{{ $sala->numero }}</option>
                        @endforeach
                    </select>

                </div>
                <div>
                    <label for="campoDia" class="form-label mt-3">Dia da semana:</label>
                    <select class="form-select" aria-label="Default select example" id="campoDia" name="campoDia">
                        <option value="" disabled selected>Selecione</option>
                        <option value="1" >Segunda-feira</option>
                        <option value="2" >Terça-feira</option>
                        <option value="3" >Quarta-feira</option>
                        <option value="4" >Quinta-feira</option>
                        <option value="5" >Sexta-feira</option>
                    </select>
               </div>
            </div>
            <div class="nome">
                <div>
                    <label for="campoHoraIni" class="form-label mt-3">Horário início:</label>
                    <input type="time" class="form-control" name="campoHoraIni" value="{{old('campoHoraIni')}}" min="08:00" max="22:00" id="campoHoraIni" required>
                </div>
                <div>
                    <label for="campoHoraFim" class="form-label mt-3">Horário fim:</label>
                    <input type="time" class="form-control" name="campoHoraFim" value="{{old('campoHoraFim')}}" min="08:00" max="22:00" id="campoHoraFim" required>
                </div>
            </div>
            <div>
                <label for="campoDescricao" class="form-label mt-3">Descrição:</label>
                <textarea rows="7" class="form-control" style="resize: none;" name="campoDescricao" value="{{old('campoDescricao')}}" id="campoDescricao" required>
                </textarea>
            </div>
            <div class="d-flex justify-content-end mt-3 mb-3">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </div>
        </form>

</main>

<script>
    document.getElementById('campoHoraIni').addEventListener('change', function() {
        // Obter o valor selecionado no campo de horário inicial
        const horaInicio = this.value;
        
        // Configurar o valor mínimo do campo de horário final para o valor do horário inicial
        const campoHoraFim = document.getElementById('campoHoraFim');
        campoHoraFim.min = horaInicio;
        
        // Limpar o valor do horário final se for menor que o novo mínimo
        if (campoHoraFim.value && campoHoraFim.value < horaInicio) {
            campoHoraFim.value = '';
        }
    });
</script>
@endsection
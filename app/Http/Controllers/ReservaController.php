<?php

namespace App\Http\Controllers;

use App\Models\Equipamentos;
use App\Models\Equipamentos_reserva;
use App\Models\ReservaDiretoria;
use App\Models\ReservaProf;
use App\Models\ReservaProRei;
use App\Models\Salas;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    function profReserva(){
        $salas = Salas::all();
        return view('reservas.professor', compact('salas'));
    }

    public function verificarReservaProf($ini, $fim, $sala, $data){
        $verifica = ReservaProf::where('id_sala', $sala)
            ->where('data', $data)
            ->where(function ($query) use ($ini, $fim) {
                $query->where(function ($q) use ($ini) {
                    $q->whereTime('horario_inicio', '<=', $ini)
                      ->whereTime('horario_fim', '>=', $ini);
                })->orWhere(function ($q) use ($fim) {
                    $q->whereTime('horario_inicio', '<=', $fim)
                      ->whereTime('horario_fim', '>=', $fim);
                });
            })
            ->where('status', 'aprovado')
            ->first();
    
        return $verifica;        
    }
    
    public function verificarReservaProRei($ini, $fim, $sala, $data){
        $verifica = ReservaProRei::where('id_sala', $sala)
            ->where('data', $data)
            ->where(function ($query) use ($ini, $fim) {
                $query->where(function ($q) use ($ini) {
                    $q->whereTime('horario_inicio', '<=', $ini)
                      ->whereTime('horario_fim', '>=', $ini);
                })->orWhere(function ($q) use ($fim) {
                    $q->whereTime('horario_inicio', '<=', $fim)
                      ->whereTime('horario_fim', '>=', $fim);
                });
            })
            ->where('status', 'aprovado')
            ->first();
    
        return $verifica;        
    }

    function profStore(Request $request){
        $register = new ReservaProf();

        if($this->verificarReservaProf($request->campoHoraIni, $request->campoHoraFim, $request->campoSala, $request->campoData)){
            return redirect()->back()->with('error', 'Sala ocupada nesse horário!');
        }
        if($this->verificarReservaProRei($request->campoHoraIni, $request->campoHoraFim, $request->campoSala, $request->campoData)){
            return redirect()->back()->with('error', 'Sala ocupada nesse horário!');
        }

        $register->id_sala = $request->campoSala;
        $register->data = $request->campoData;
        $register->horario_inicio = $request->campoHoraIni;
        $register->horario_fim = $request->campoHoraFim;
        $register->descricao = $request->campoDescricao;
        $register->id_professor = auth()->User()->id_usuario;
        $register->primeiro_nome = auth()->User()->primeiro_nome;
        $register->status = 'aguardando aprovação';


        $register->save();

        return redirect()->back()->with('success', 'Reserva solicitada, aguarde a autorização da Prefeitura!');
    }


    function proReiReserva(){
        $salas = Salas::all();
        return view('reservas.proReitoria', compact('salas'));
    }

    function proReiStore(Request $request){
        $register = new ReservaProRei();

        if($this->verificarReservaProf($request->campoHoraIni, $request->campoHoraFim, $request->campoSala, $request->campoData)){
            return redirect()->back()->with('error', 'Sala ocupada nesse horário!');
        }
        if($this->verificarReservaProRei($request->campoHoraIni, $request->campoHoraFim, $request->campoSala, $request->campoData)){
            return redirect()->back()->with('error', 'Sala ocupada nesse horário!');
        }

        $register->id_sala = $request->campoSala;
        $register->data = $request->campoData;
        $register->horario_inicio = $request->campoHoraIni;
        $register->horario_fim = $request->campoHoraFim;
        $register->descricao = $request->campoDescricao;
        $register->id_pro_reitoria = auth()->User()->id_usuario;
        $register->status = 'aprovado';


        $register->save();

        return redirect()->back()->with('success', 'Parabéns! Sua reserva foi aprovada com sucesso.');
    }

    public function showReservas(){
        $reservas = ReservaProf::where('status', 'aguardando aprovação')->get();
        return view('reservas.aguardando', compact('reservas'));
    }

    public function aceitarReserva($id){
        $reserva = ReservaProf::findOrFail($id);
        $reserva->status = 'aprovado';
        $reserva->save();
        return redirect()->route('PreReservaSalas')->with('success', 'Reserva aprovada com sucesso!');
    }

    public function minhasReservas(){
        $id = auth()->User()->id_usuario;
        if(auth()->User()->tipo == 2){
            $reservas_equipamentos = Equipamentos_reserva::where('id_professor', $id)->get();
            $reservas = ReservaProf::where('id_professor', $id)->get();
            return view('reservas.minhas', compact('reservas', 'reservas_equipamentos'));
        }elseif(auth()->User()->tipo == 4){
            $reservas = ReservaProRei::where('id_pro_reitoria', $id)->get();
            return view('reservas.minhas', compact('reservas'));
        }else {
            $reservas = ReservaProf::where('id_professor', $id)->get();
            return view('reservas.minhas', compact('reservas'));
        }
    }

    public function cancelarReserva($id, Request $request){
        $reserva = ReservaProf::findOrFail($id);
        $reserva->status = 'cancelado';
        $reserva->motivo_cancelamento = $request->motivo_cancelamento;
        $reserva->save();
        return redirect()->route('PreReservaSalas')->with('success', 'Reserva cancelada com sucesso!');
    }

    public function aprovadas(){
        $reservas = ReservaProf::where('status', 'aprovado')->get(); 
        $reservasProRei = ReservaProRei::where('status', 'aprovado')->get();

        return view('reservas.aprovadas', compact('reservas', 'reservasProRei'));
    }

    public function calendario(){
        $reservas = collect(ReservaProf::where('status', 'aprovado')->get());
        $reservasProRei = collect(ReservaProRei::where('status', 'aprovado')->get());
        $salas = Salas::all();

        $eventos = $reservas->map(function ($reserva) {
            return [
                'title' => $reserva->primeiro_nome,  // Nome da reserva (pode ser alterado conforme a necessidade)
                'start' => $reserva->data . 'T' . $reserva->horario_inicio,  // Data e hora de início
                'end' => $reserva->data . 'T' . $reserva->horario_fim, 
                'description' => $reserva->descricao ?? 'Sem descrição',
                'local' => $reserva->id_sala,
            ];
        })->merge($reservasProRei->map(function ($reservaProRei) {
            return [
                'title' => 'Pró-Reitoria',  // Título fixo para a reserva da Pró-Reitoria
                'start' => $reservaProRei->data . 'T' . $reservaProRei->horario_inicio,  // Data e hora de início
                'end' => $reservaProRei->data . 'T' . $reservaProRei->horario_fim, 
                'description' => $reservaProRei->descricao ?? 'Sem descrição',  // Descrição da reserva, caso exista
                'local' => $reservaProRei->id_sala,
            ];
        }));
    
        // Envia os eventos para a view
        return view('calendario', compact('eventos', 'salas'));
    }

    public function viewEquipamento()
    {
        $id = auth()->User()->id_usuario;
        $reservas = Equipamentos_reserva::where('id_professor',$id)->get();
        $equipamentos = Equipamentos::all();
        return view('reservas.equipamentos', compact('equipamentos', 'reservas'));
    }

    public function getEspecificacoes($tipo)
    {
        $equipamentos = Equipamentos::where('nome', $tipo)->get();
        
        return response()->json($equipamentos->pluck('descricao', 'id_equipamentos'));
    }

    public function reservarEquipamento(Request $request)
    {
        $equipamento = $request->input('campoEspecificacao');
        if (!is_numeric($equipamento)) {
            return redirect()->back()->with('error', 'O ID do equipamento é inválido.');
        }
        $data = $request->input('campoData');
        $horario_inicio = $request->input('campoHoraIni');
        $horario_fim = $request->input('campoHoraFim');
        $descricao = $request->input('campoDescricao');

        $reserva = new Equipamentos_reserva();
        $reserva->id_equipamentos = $equipamento;
        $reserva->data = $data;
        $reserva->horario_inicio = $horario_inicio;
        $reserva->horario_fim = $horario_fim;
        $reserva->descricao = $descricao;
        $reserva->id_professor = auth()->User()->id_usuario;
        $reserva->save();

        return redirect()->back()->with('success', 'Reserva realizada com sucesso!');
    }

    public function viewReservaDiretor(){
        $salas = Salas::all();
        return view('reservas.diretor', compact('salas'));
    }

    public function reservadiretoria(Request $request){
        $ano = $request->campoAno;
        $diaSemana = $request->campoDia; // O dia da semana selecionado (1=Segunda, 2=Terça, ...)
        $periodo = $request->campoPeriodo; // 1=1º semestre (Jan-Junho), 2=2º semestre (Jul-Dez)
        $salas = $request->campoSala; // Lista de salas selecionadas

        // Valida se os dados são adequados
        if (empty($ano) || empty($diaSemana) || empty($periodo) || empty($salas)) {
            return redirect()->back()->with('error', 'Todos os campos são obrigatórios.');
        }

        // Define o intervalo de meses baseado no semestre
        if ($periodo == 1) {
            $meses = range(1, 6);  // Janeiro a Junho
        } else {
            $meses = range(7, 12); // Julho a Dezembro
        }

        // Laço para percorrer todas as salas selecionadas
        foreach ($salas as $sala) {
            // Laço para percorrer todos os meses do período selecionado
            foreach ($meses as $mes) {
                // Obter todas as datas do mês que correspondem ao dia da semana escolhido
                $diasDoMes = $this->obterDiasDaSemana($ano, $mes, $diaSemana);

                // Laço para percorrer todos os dias da semana no mês
                foreach ($diasDoMes as $dia) {
                    // Cria a reserva para cada combinação de sala, dia e período
                    $register = new ReservaDiretoria();

                    // Concatena o ano, mês e dia em uma data única no formato Y-m-d
                    $dataReserva = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '-' . str_pad($dia, 2, '0', STR_PAD_LEFT);

                    // Atribui os dados
                    $register->id_sala = $sala;
                    $register->horario_inicio = $request->campoHoraIni;  // Hora de início
                    $register->horario_fim = $request->campoHoraFim;    // Hora de fim
                    $register->descricao = $request->campoDescricao;
                    $register->id_diretor = auth()->User()->id_usuario;
                    $register->data = $dataReserva; // Salva a data completa
                    $register->periodo = $periodo; // Período (1 ou 2)

                    // Salva a reserva
                    $register->save();
                }
            }
        }

        return redirect()->back()->with('success', 'Parabéns! Sua reserva foi aprovada com sucesso.');
    }
}

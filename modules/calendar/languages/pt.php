<?php
//------------------------------------------------------------------------------             
//*** Portuguese (pt)
//------------------------------------------------------------------------------
function setLanguage(){ 
    
	$lang['all_available'] = "Disponível todo o";
	$lang['partially_booked'] = "Parcialmente Reservado";
	$lang['not_avaliable'] = "Não disponível";
	$lang['fully_booked'] = "Iotalmente reservado";
	$lang['legend'] = "Lenda";
	$lang['rooms'] = "quartos";
	$lang['with_reserved'] = "Com Reservado";
	$lang['without_reserved'] = "Sem Reservados";
	$lang['bookings'] = "Reservas";
	$lang['all_rooms'] = "Todos os Quartos";
	$lang['reserved_and_completed'] = "Reservados e Concluído";
	$lang['completed_only'] = "Apenas concluída";
	
	$lang['actions'] = "Ações";
	$lang['add_category'] = "Adicionar categoria";
	$lang['add_event'] = "Adicionar Evento";
	$lang['add_new_category'] = "Adicionar nova categoria";
    $lang['add_new_event'] = "Adicionar Novo Evento";
	$lang['back'] = "De volta";
	$lang['cancel'] = "Cancelar";
	$lang['category_color'] = "Cor da Categoria";
	$lang['category_description'] = "Descrição da Categoria";
	$lang['category_details'] = "Detalhes categoria";
	$lang['category_name'] = "Nome da Categoria";
	$lang['categories'] = "Categorias";
	$lang['categories_events'] = "Categorias eventos";	
	$lang['click_to_delete'] = "Clique para excluir";
	$lang['chart_bar'] = "Gráfico de Colunas";
	$lang['chart_column'] = "Gráfico de Barras";
	$lang['chart_pie'] = "Carta de torta";
	$lang['click_view_week'] = "Clique para ver esta semana";
	$lang['click_to_print'] = "Clique para imprimir";
	$lang['close'] = "Fechar";
	$lang['close_lc'] = "fechar";
	$lang['collapse'] = "Colapso";
	$lang['debug_info'] = "Depurar informações";
	$lang['default'] = "omissão";
	$lang['details'] = "Detalhes";
	$lang['delete'] = "Deletar";
	$lang['delete_events'] = "Apagar Eventos";
	$lang['delete_by_range'] = "Excluir por Faixa";
	$lang['duration'] = "Duração";	
	$lang['edit'] = "Editar";
	$lang['edit_category'] = "Editar Categoria";
	$lang['edit_event'] = "Editar Evento";
	$lang['order_lc'] = "ordem";
	$lang['orders_lc'] = "ordens";
	$lang['events_categories'] = "Categorias Eventos";
	$lang['event_name'] = "Nome do Evento";
	$lang['event_date'] = "Data do Evento";
	$lang['event_time'] = "Hora do Evento";
	$lang['event_description'] = "Descrição do Evento";
	$lang['event_details'] = "Detalhes do Evento";
	$lang['events'] = "Eventos";
	$lang['events_management'] = "Gestão de Eventos";
	$lang['events_statistics'] = "Estatísticas Eventos";
	$lang['expand'] = "Expandir";
	$lang['from'] = "De";
	$lang['go'] = "Ir";
	$lang['hours'] = "Horas";
	$lang['manage_events'] = "Gerenciar Eventos";
	$lang['not_defined'] = "não definida";
	$lang['occurrences'] = "Ocorrências";
	$lang['one_time'] = "Apenas uma vez";
	$lang['or'] = "ou";
	$lang['pages'] = "Páginas";
	$lang['print'] = "Imprimir";
	$lang['repeat_every'] = "Repetir todos";
	$lang['repeatedly'] = "Repetidamente";
	$lang['select'] = "selecionar";
	$lang['select_event'] = "selecione o evento";
	$lang['show_all'] = "mostrar todos";	
	$lang['select_category'] = "selecionar categoria";
	$lang['select_chart_type'] = "Selecione o tipo de gráfico";
	$lang['start_time'] = "Hora de Início";
	$lang['statistics'] = "Estatística";
	$lang['th'] = "to"; // suffix for dates, like: 25th
	$lang['to'] = "Do";
	$lang['today'] = "Hoje";
	$lang['top_10_events'] = "Top 10 eventos";
	$lang['total_events'] = "Total de Eventos";
	$lang['total_categories'] = "Total de Categorias";
	$lang['total_running_time'] = "Tempo total";
	$lang['undefined'] = "Indefinido";
	$lang['update'] = "Atualizar";
	$lang['update_category'] = "Atualize Categoria";
	$lang['update_event'] = "Atualize evento";
	$lang['view'] = "Ver";
	$lang['view_events'] = "Ver Eventos";
	$lang['select_hotel'] = "Escolha um Hotel"; 
	
	$lang['lbl_add_event_to_list'] = "Basta adicionar à lista de eventos";
	$lang['lbl_add_event_occurrences'] = "Adicionar ocorrências para este evento";

	$lang['msg_editing_event_in_past'] = "Acontecimento não pode ser adicionado no tempo passado! Por favor, re-entrar.";
	$lang['msg_this_operation_blocked'] = "Esta operação é bloqueado!";
	$lang['msg_this_operation_blocked_demo'] = "Esta operação é bloqueada na versão DEMO!";
	$lang['msg_timezone_invalid'] = "'_TIME_ZONE_' ID fuso horário é inválido.";
	$lang['msg_view_type_invalid'] = "'_DEFAULT_VIEW_' Ver padrão não era permitido! Selecione outro.";

    $lang['error_inserting_new_events'] = "Ocorreu um erro durante a inserção de novos eventos! Por favor, tente novamente mais tarde.";
	$lang['error_inserting_new_category'] = "Ocorreu um erro ao inserir nova categoria! Por favor, tente novamente mais tarde.";
    $lang['error_deleting_event'] = "Ocorreu um erro durante a exclusão de evento! Por favor, tente novamente mais tarde.";
	$lang['error_duplicate_event_inserting'] = "Evento com esse nome já foi adicionado ao período escolhido! Por favor, escolha outro.";
	$lang['error_duplicate_events_inserting'] = "Período de tempo selecionado já está ocupado! Por favor, escolha outro.";
    $lang['error_updating_event'] = "Ocorreu um erro durante a atualização do evento! Por favor, tente novamente mais tarde.";
	$lang['error_event_exists'] = "Evento com esse nome já existe! Por favor, escolha outro nome.";
	$lang['error_category_exists'] = "Categoria com esse nome já existe! Por favor, escolha outro nome.";
	$lang['error_from_to_hour'] = "'De' hora não pode ser maior do que 'Para' hora! Por favor, re-entrar.";
    $lang['error_updating_category'] = "Ocorreu um erro durante a atualização categoria! Por favor, tente novamente mais tarde.";
	$lang['error_deleting_category'] = "Ocorreu um erro durante a exclusão de categoria! Por favor, tente novamente mais tarde.";
	$lang['error_deleting_event_hours'] = "Não é possível excluir evento! Menos de _HOURS_ horas manteve-se.";	
	$lang['error_deleting_event_past'] = "Não é possível excluir eventos no passado!";
	$lang['error_no_event_found'] = "Não foram encontrados eventos!";
	$lang['error_no_dates_found'] = "Sem datas foi encontrado para inserir evento! Por favor, re-entrar.";

    $lang['success_new_event_was_added'] = "Novo evento foi adicionado com sucesso!";
    $lang['success_event_was_deleted'] = "'_EVENT_NAME_' Evento foi excluído com sucesso!";
	$lang['success_events_were_deleted'] = "Eventos para determinado período de tempo foram excluído com sucesso!";
    $lang['success_event_was_updated'] = "Evento foi atualizado com sucesso!";
	$lang['success_new_category_added'] = "Nova categoria foi adicionada com sucesso!";
	$lang['success_category_was_updated'] = "Categoria foi atualizada com sucesso!";
    $lang['success_category_was_deleted'] = "Categoria foi excluído com sucesso!";

    
    // date-time
    $lang['day']    = "dia";
    $lang['month']  = "mês";
    $lang['year']   = "ano";
    $lang['hour']   = "hora";
    $lang['min']    = "min";
    $lang['sec']    = "seg";
    
    $lang['daily']     = "Diário";
    $lang['weekly']    = "Semanal";
    $lang['monthly']   = "Mensal";
    $lang['yearly']    = "Anual";
	$lang['list_view'] = "Ver lista";

    $lang['sun'] = "Dom";
	$lang['mon'] = "Seg";
	$lang['tue'] = "Ter";
	$lang['wed'] = "Qua";
	$lang['thu'] = "Qui";
	$lang['fri'] = "Sxt";
	$lang['sat'] = "Sáb";    

    $lang['sunday'] = "Domingo";
	$lang['monday'] = "Segunda-feira";
	$lang['tuesday'] = "Terça-feira";
	$lang['wednesday'] = "Quarta-feira";
	$lang['thursday'] = "Quinta-feira";
	$lang['friday'] = "Sexta-feira";
	$lang['saturday'] = "Sábado";    
    
    $lang['months'][1] = "Janeiro";
    $lang['months'][2] = "Fevereiro";
    $lang['months'][3] = "Março";
    $lang['months'][4] = "Abril";
    $lang['months'][5] = "Maio";
    $lang['months'][6] = "Junho";
    $lang['months'][7] = "Julho";
    $lang['months'][8] = "Agosto";
    $lang['months'][9] = "Setembro";
    $lang['months'][10] = "Outubro";
    $lang['months'][11] = "Novembro";
    $lang['months'][12] = "Dezembro";
    
    return $lang;
}

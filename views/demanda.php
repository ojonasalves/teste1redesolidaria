<?php
$id=(int)($_GET['id']??0);

$s=db()->prepare(
    "SELECT d.*,o.nome_fantasia,o.usuario_id AS ong_usuario_id
     FROM demandas d
     JOIN ongs o ON o.id=d.ong_id
     WHERE d.id=?"
);

$s->execute([$id]);
$d=$s->fetch();

if(!$d){
    http_response_code(404);
    exit('Demanda não encontrada.');
}

if(is_post()){

    require_role('solidario');
    check_csrf();

    /* Verificar novamente se a demanda continua aberta */
    if($d['status']!=='aberta'){
        flash('error','Esta demanda já foi encerrada e não aceita novos interessados.');
        redirect_to(url('demanda',['id'=>$id]));
    }

    /* Verificar se o Solidário já demonstrou interesse */
    $c=db()->prepare(
        "SELECT id
         FROM participacoes
         WHERE demanda_id=? AND solidario_id=?"
    );

    $c->execute([
        $id,
        current_user()['id']
    ]);

    if($c->fetch()){

        flash('error','Você já demonstrou interesse.');

    }else{

        /*
         * Registrar participação
         */
        db()->prepare(
            "INSERT INTO participacoes
             (demanda_id,solidario_id,status)
             VALUES(?,?, 'interessado')"
        )->execute([
            $id,
            current_user()['id']
        ]);

        /*
         * Criar notificação para a ONG
         */
        db()->prepare(
            "INSERT INTO notificacoes
             (usuario_id,titulo,mensagem)
             VALUES(?,?,?)"
        )->execute([
            $d['ong_usuario_id'],
            'Novo interessado',
            'Um Solidário demonstrou interesse na sua demanda "'.$d['titulo'].'".'
        ]);

        flash('success','Interesse registrado! A ONG foi notificada.');
    }

    redirect_to(url('demanda',['id'=>$id]));
}

$title=$d['titulo'].' - '.APP_NAME;

require __DIR__.'/../includes/header.php';
?>

<div class="card">

    <span class="badge">
        <?=e($d['categoria'])?>
    </span>

    <h1><?=e($d['titulo'])?></h1>

    <p>
        <?=nl2br(e($d['descricao']))?>
    </p>

    <p>
        <b>ONG:</b> <?=e($d['nome_fantasia'])?>
    </p>

    <p>
        <b>Cidade:</b> <?=e($d['cidade'])?>
    </p>

    <p>
        <b>Prazo:</b>
        <?=e($d['prazo']?:'Não informado')?>
    </p>

    <?php if(current_user()&&current_user()['tipo']==='solidario'&&$d['status']==='aberta'): ?>

        <form method="post">

            <input
                type="hidden"
                name="csrf"
                value="<?=e(csrf())?>"
            >

            <button class="btn">
                Quero ajudar
            </button>

        </form>

    <?php elseif(current_user()&&$d['status']==='encerrada'): ?>

        <p>
            <span class="badge">
                Demanda encerrada
            </span>
        </p>

    <?php elseif(!current_user()): ?>

        <a class="btn" href="<?=url('login')?>">
            Entrar para ajudar
        </a>

    <?php endif; ?>

</div>

<?php require __DIR__.'/../includes/footer.php'; ?>
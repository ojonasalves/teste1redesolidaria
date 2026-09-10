<?php

$title='Demandas - '.APP_NAME;

require __DIR__.'/../includes/header.php';

?>

<h1>Demandas sociais</h1>

<div class="card">

    <form method="get" action="index.php">

        <input type="hidden" name="page" value="demandas">

        <label>
            Buscar

            <input
                type="text"
                name="q"
                placeholder="Digite uma palavra..."
            >
        </label>

        <label>
            Cidade

            <input
                type="text"
                name="cidade"
                placeholder="Ex.: Santa Teresa"
            >
        </label>

        <label>
            Categoria

            <select name="categoria">

                <option value="">
                    Todas as categorias
                </option>

                <option value="Alimentação">
                    Alimentação
                </option>

                <option value="Roupas">
                    Roupas
                </option>

                <option value="Educação">
                    Educação
                </option>

                <option value="Saúde">
                    Saúde
                </option>

                <option value="Moradia">
                    Moradia
                </option>

                <option value="Meio ambiente">
                    Meio ambiente
                </option>

                <option value="Animais">
                    Animais
                </option>

                <option value="Doações">
                    Doações
                </option>

                <option value="Voluntariado">
                    Voluntariado
                </option>

                <option value="Outros">
                    Outros
                </option>

            </select>

        </label>

        <button class="btn" type="submit">
            Buscar
        </button>

    </form>

</div>

<?php

$q=trim($_GET['q']??'');
$cidade=trim($_GET['cidade']??'');
$categoria=trim($_GET['categoria']??'');

$sql="
    SELECT
        d.*,
        o.nome_fantasia
    FROM demandas d
    JOIN ongs o ON o.id=d.ong_id
    WHERE d.status='aberta'
";

$params=[];

if($q!==''){

    $sql.="
        AND (
            d.titulo LIKE ?
            OR d.descricao LIKE ?
            OR d.categoria LIKE ?
            OR d.cidade LIKE ?
        )
    ";

    $termo='%'.$q.'%';

    $params[]=$termo;
    $params[]=$termo;
    $params[]=$termo;
    $params[]=$termo;
}

if($cidade!==''){

    $sql.="
        AND d.cidade LIKE ?
    ";

    $params[]='%'.$cidade.'%';
}

if($categoria!==''){

    $sql.="
        AND d.categoria=?
    ";

    $params[]=$categoria;
}

$sql.=" ORDER BY d.id DESC";

$s=db()->prepare($sql);

$s->execute($params);

$items=$s->fetchAll();

?>

<?php if($items): ?>

<div class="grid3">

<?php foreach($items as $d): ?>

<div class="card">

    <span class="badge">
        <?=e($d['categoria'])?>
    </span>

    <h2>
        <?=e($d['titulo'])?>
    </h2>

    <p>
        <?=e($d['descricao'])?>
    </p>

    <p>
        <b>ONG:</b>
        <?=e($d['nome_fantasia'])?>
    </p>

    <p>
        <b>Cidade:</b>
        <?=e($d['cidade'])?>
    </p>

    <p>
        <b>Prazo:</b>
        <?=e($d['prazo'] ?: 'Não informado')?>
    </p>

    <a
        class="btn small"
        href="<?=url('demanda',['id'=>$d['id']])?>"
    >
        Ver demanda
    </a>

</div>

<?php endforeach; ?>

</div>

<?php else: ?>

<div class="card">

    <h2>Nenhuma demanda encontrada.</h2>

    <p>
        Não encontramos demandas com os filtros informados.
    </p>

</div>

<?php endif; ?>

<?php require __DIR__.'/../includes/footer.php'; ?>
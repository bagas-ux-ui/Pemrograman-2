
<!DOCTYPE html>
<html>
<head>
    <title>Kalkulator Sederhana</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: white;
        }

        .container {
            width: 730px;
            margin: 50px auto;
        }

        .judul {
            display: flex;
            justify-content: space-around;
            margin-bottom: 45px;
            color: darkred;
            font-family: Georgia, serif;
            font-size: 30px;
            font-weight: bold;
        }

        form {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        input[type="number"] {
            width: 270px;
            height: 38px;
            font-size: 20px;
            box-sizing: border-box;
        }

        select {
            height: 43px;
            width: 55px;
            font-size: 20px;
            background-color: white;
            color: black;
            border: 1px solid #999;
            outline: none;
        }

        select option {
            background-color: white;
            color: black;
        }

        select option:checked {
            background-color: #e0e0e0;
            color: black;
        }

        button {
            height: 43px;
            font-size: 20px;
            cursor: pointer;
            background-color: #f5f5f5;
            color: black;
            border: 1px solid #999;
            border-radius: 3px;
        }

        button:hover {
            background-color: #e5e5e5;
        }

        .hasil {
            margin-top: 25px;
            font-size: 20px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="judul">
        <span>Nilai I</span>
        <span>Nilai II</span>
    </div>

    <form method="post">
        <input
            type="number"
            name="nilai1"
            step="any"
            value="<?php echo isset($_POST['nilai1']) ? htmlspecialchars($_POST['nilai1']) : ''; ?>"
            required
        >

        <select name="operator">
            <option value="+" <?php if (isset($_POST['operator']) && $_POST['operator'] == '+') echo 'selected'; ?>>+</option>
            <option value="-" <?php if (isset($_POST['operator']) && $_POST['operator'] == '-') echo 'selected'; ?>>-</option>
            <option value="*" <?php if (isset($_POST['operator']) && $_POST['operator'] == '*') echo 'selected'; ?>>*</option>
            <option value="/" <?php if (isset($_POST['operator']) && $_POST['operator'] == '/') echo 'selected'; ?>>/</option>
        </select>

        <input
            type="number"
            name="nilai2"
            step="any"
            value="<?php echo isset($_POST['nilai2']) ? htmlspecialchars($_POST['nilai2']) : ''; ?>"
            required
        >

        <button type="submit" name="hitung">submit</button>
    </form>

    <?php
    if (isset($_POST['hitung'])) {
        $a = (float) $_POST['nilai1'];
        $b = (float) $_POST['nilai2'];
        $op = $_POST['operator'];

        echo '<div class="hasil">';

        switch ($op) {
            case '+':
                $hasil = $a + $b;
                break;

            case '-':
                $hasil = $a - $b;
                break;

            case '*':
                $hasil = $a * $b;
                break;

            case '/':
                if ($b == 0) {
                    echo 'Error: Nilai II tidak boleh nol untuk pembagian.';
                    echo '</div>';
                    exit;
                }
                $hasil = $a / $b;
                break;

            default:
                echo 'Operator tidak valid.';
                echo '</div>';
                exit;
        }

        echo 'Hasil: ' . $hasil;
        echo '</div>';
    }
    ?>

</div>

</body>
</html>

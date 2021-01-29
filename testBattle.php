<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('core/Main.php');
$main = new Main(false);
$database = $main->db;
$functions = $main->functions;

$currentUser = $functions->getCurrentUser();
$allMonsters = $functions->getAllMonsters();
$monsterDead = false;

if (!isset($_SESSION['battle'])) {
    $monsterData = $allMonsters[rand(0, sizeof($allMonsters) - 1)];
    $monsterData["CurrentHP"] = $monsterData["BaseHP"];
    print("<h1>You encountered a Level {$monsterData["Level"]} {$monsterData["MonsterName"]}. It has {$monsterData["CurrentHP"]}HP</h1>");
    $_SESSION['battle']['monster'] = $monsterData;
} else {
    echo "Loading Existing Battle<br>";
    $monsterData = $_SESSION['battle']['monster'];
}
print_r($monsterData);
print("<br>");

// Grab Monster Skills
$monsterSkills = $functions->getMonsterSkills($monsterData["MonsterID"]);
print_r($monsterSkills);
print("<br>");

if(sizeof($monsterSkills) <= 0) {
    unset($_SESSION['battle']);
    die("There was an error whilst initializing the battle. Please contact a member of the team. Monster ID: {$monsterData["MonsterID"]} Cause: Skills.");
}

// Get Random Monster Skill
$monsterSkill = $monsterSkills[rand(0, sizeof($monsterSkills) - 1)];
print_r($monsterSkill);
print("<br>");

// Grab the Data
$monsterSkillData = $functions->getSkillData($monsterSkill[2])->fetch_array();
print_r($monsterSkillData);
print("<br>");

// Grab User Skills
$userSkills = $functions->getUserSkills($currentUser["id"]);
$userSkillsHTML = "";
foreach ($userSkills as $skill) {
    $skillData = $functions->getSkillData($skill[2])->fetch_array();
    $userSkillsHTML .= '<input type="radio" id="'.$skillData["Name"].'" name="skill" value="'.$skillData["id"].'"><label for="'.$skillData["Name"].'">'.$skillData["Name"].'</label><br>';
}

print_r($userSkills);
print("<br>");

if(sizeof($userSkills) <= 0) {
    die("You don't have any skills. Please contact the team. UserID: {$currentUser["id"]}");
}

if (isset($_POST['attack'])) {
    $userSpeed = $functions->getOverallUserSpeedStat($currentUser['id']);
    if(is_array($userSpeed)) {
        die("There was an error whilst fetching the users speed. Please contact staff. UserID: {$currentUser['id']}");
    }
    print_r($userSpeed);

    // Monster attacks the user depending on user speed stat
    if($userSpeed == $monsterData['BaseSpeed']) {
        // Randomly select who is going first.
        $randomVal = rand(1,100);
        if($randomVal <= 50) {
            // Monster goes first
            attackPlayer();
            // Then the player attacks the monster
            attackMonster();
        } else {
            // User goes first.
            attackMonster();
            // Then the monster attacks the player providing that the monster isn't dead.
            attackPlayer();
        }
    } else if($userSpeed < $monsterData['BaseSpeed']) {
        // Monster goes first
        attackPlayer();
        // Then the player attacks the monster
        attackMonster();
    } else {
        // User goes first.
        attackMonster();
        // Then the monster attacks the player providing that the monster isn't dead.
        attackPlayer();
    }
}

function attackMonster() {
    global $monsterDead, $monsterData, $functions, $currentUser;

    echo "Called attack monster";

    $getSkillData = $functions->getSkillData($_POST['skill'])->fetch_array();
    print_r($getSkillData);
    if(sizeof($getSkillData) <= 0) {
        die("There was an error fetching user skills. Please contact the team. User ID: {$currentUser['id']}.");
    }

    $damage = $functions->getOverallDamage($getSkillData['id'], $currentUser["id"]);

    if(is_array($damage)) {
        die("There was an error when calculating user damage. Please contact staff. SkillID: {$getSkillData['id']} UserID: {$currentUser['id']}");
    }

    $monsterData['CurrentHP'] = $monsterData['CurrentHP'] - $damage;
    print("<h1>You dealt {$damage} attack points with {$getSkillData["Name"]} against {$monsterData["MonsterName"]}</h1>");
    if ($monsterData['CurrentHP'] > 0) {
        $_SESSION['battle']['monster'] = $monsterData;
        print("<p>The {$monsterData["MonsterName"]} has {$monsterData["CurrentHP"]}HP remaining!</p>");
    } else {
        unset($_SESSION['battle']);
        print("<p>{$monsterData["MonsterName"]} has been slain.</p>");
        $monsterDead = true;
    }
}

function attackPlayer() {
    global $monsterDead;
    if(!$monsterDead) {
        echo "Called attack player";
    }
}

if(isset($_POST['flee'])) {
    unset($_SESSION['battle']);
    print("<p>You have fled the battle.</p>");
    $monsterDead = true;
}
?>

<?php
if(!$monsterDead){
?>
<form method="post">
    <?php echo $userSkillsHTML; ?>
    <input type="submit" name="attack" value="Attack!"/>
    <input type="submit" name="flee" value="Flee!"/>
</form>
<?php } else { ?>
    <a href="<?php echo $main->getWorkingDirectory(); ?>areas.php">Return to Areas</a>
<?php } ?>

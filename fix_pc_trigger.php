<?php
require_once 'config/config.php';

echo "<h2>Fixing SITIn PC Management Trigger Issues</h2>";

// Step 1: Add last_used column if it doesn't exist
echo "<h3>Step 1: Adding last_used column to pcs table</h3>";
try {
    $addColumnSQL = "ALTER TABLE pcs ADD COLUMN last_used TIMESTAMP NULL DEFAULT NULL AFTER status";
    
    if ($conn->query($addColumnSQL)) {
        echo "<p style='color:green'>Successfully added last_used column.</p>";
    } else {
        if ($conn->errno == 1060) { // Duplicate column
            echo "<p>The last_used column already exists.</p>";
        } else {
            echo "<p style='color:orange'>Warning: " . $conn->error . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:orange'>Warning: " . $e->getMessage() . "</p>";
}

// Step 2: Drop and recreate the problematic trigger
echo "<h3>Step 2: Fixing the update_pc_status_on_sitin trigger</h3>";
try {
    // First drop the existing trigger
    $dropTriggerSQL = "DROP TRIGGER IF EXISTS update_pc_status_on_sitin";
    if ($conn->query($dropTriggerSQL)) {
        echo "<p style='color:green'>Successfully dropped existing trigger.</p>";
    } else {
        echo "<p style='color:red'>Error dropping trigger: " . $conn->error . "</p>";
    }
    
    // Create new trigger that doesn't update sit_in table
    $createTriggerSQL = "
    CREATE TRIGGER update_pc_status_on_sitin
    AFTER INSERT ON sit_in
    FOR EACH ROW
    BEGIN
        DECLARE pc_id_var INT;
        
        IF NEW.pc_number IS NOT NULL THEN
            SELECT p.pc_id INTO pc_id_var
            FROM pcs p
            JOIN labs l ON p.lab_id = l.lab_id
            WHERE l.lab_name = NEW.lab AND p.pc_number = NEW.pc_number
            LIMIT 1;
            
            IF pc_id_var IS NOT NULL THEN
                UPDATE pcs SET status = 'unavailable';
                
                IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
                          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pcs' AND COLUMN_NAME = 'last_used')
                THEN
                    UPDATE pcs SET last_used = CURRENT_TIMESTAMP 
                    WHERE pc_id = pc_id_var;
                ELSE
                    UPDATE pcs SET status = 'unavailable'
                    WHERE pc_id = pc_id_var;
                END IF;
                
                -- Not updating sit_in table here to avoid the error
            END IF;
        END IF;
    END";
    
    if ($conn->query($createTriggerSQL)) {
        echo "<p style='color:green'>Successfully created new trigger without sit_in table update.</p>";
    } else {
        echo "<p style='color:red'>Error creating trigger: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
}

// Step 3: Create the stored procedure to update sit_in
echo "<h3>Step 3: Creating stored procedure for sit_in updates</h3>";
try {
    $createProcedureSQL = "
    CREATE PROCEDURE IF NOT EXISTS update_sitin_pc_id(IN sitin_id INT, IN pc_number_val INT, IN lab_name_val VARCHAR(50))
    BEGIN
        DECLARE pc_id_var INT;
        
        SELECT p.pc_id INTO pc_id_var
        FROM pcs p
        JOIN labs l ON p.lab_id = l.lab_id
        WHERE l.lab_name = lab_name_val AND p.pc_number = pc_number_val
        LIMIT 1;
        
        IF pc_id_var IS NOT NULL THEN
            UPDATE sit_in SET pc_id = pc_id_var WHERE sit_in_id = sitin_id;
        END IF;
    END";
    
    if ($conn->query($createProcedureSQL)) {
        echo "<p style='color:green'>Successfully created stored procedure.</p>";
    } else {
        echo "<p style='color:red'>Error creating procedure: " . $conn->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
}

echo "<h3>Fix completed!</h3>";
echo "<p>The following changes were made:</p>";
echo "<ol>";
echo "<li>Added last_used column to pcs table (if needed)</li>";
echo "<li>Fixed the update_pc_status_on_sitin trigger to avoid updating sit_in table directly</li>";
echo "<li>Created a stored procedure update_sitin_pc_id to handle sit_in.pc_id updates</li>";
echo "</ol>";

echo "<p>You can now use the PC management functionality without triggering the error.</p>";
echo "<p><a href='admin/manage_reservations.php'>Return to Manage Reservations</a></p>";
?>

import os
import sqlite3

def create_vlan_on_switch(switch_ip, vlan_id, vlan_name):
    """Create a VLAN on the specified switch using command line."""
    commands = f"""
    enable
    configure terminal
    vlan {vlan_id}
    name {vlan_name}
    exit
    exit
    """

    os.system(f"echo '{commands}' | telnet {switch_ip}")

    print(f"VLAN {vlan_id} created with name '{vlan_name}'.")

def save_vlan_info(vlan_id, vlan_name):
    """Save VLAN information to the SQLite database."""
    try:
        conn = sqlite3.connect('vlans.db')
        cursor = conn.cursor()

        cursor.execute('''
            CREATE TABLE IF NOT EXISTS vlans (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL
            )
        ''')

        cursor.execute('INSERT INTO vlans (id, name) VALUES (?, ?)', (vlan_id, vlan_name))
        conn.commit()
        conn.close()
        print(f"VLAN {vlan_id} saved to database.")
    except Exception as e:
        print(f"Failed to save VLAN info: {e}")

def main():
    switch_ip = input("Enter Switch IP Address: ")
    vlan_id = input("Enter VLAN ID: ")
    vlan_name = input("Enter VLAN Name: ")
    
    create_vlan_on_switch(switch_ip, vlan_id, vlan_name)
    save_vlan_info(vlan_id, vlan_name)

if __name__ == "__main__":
    main()
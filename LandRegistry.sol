// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

contract LandRegistry {
    struct User {
        string username;
        bytes32 passwordHash;
    }

    struct Owner {
        string name;
        string addr;
        string phone;
        string email;
    }

    struct Land {
        string landID;
        string location;
        uint256 size;
        string landType;
        address owner;
    }

    struct Transaction {
        address newOwner;
        string transactionType;
        uint256 timestamp;
    }

    mapping(address => User) public users;
    mapping(address => Owner) public owners;
    mapping(string => Land) public lands; // Changed to map by landID
    mapping(address => Transaction[]) public transactions;

    event OwnerRegistered(address indexed ownerAddress, string name, string addr, string phone, string email);
    event LandRegistered(string indexed landID, address indexed owner, string location, uint256 size, string landType);
    event TransactionSubmitted(address indexed owner, address indexed newOwner, string transactionType, uint256 timestamp);

    function registerUser(string memory _username, string memory _password) public {
        bytes32 passwordHash = keccak256(abi.encodePacked(_password));
        users[msg.sender] = User(_username, passwordHash);
    }

    function login(string memory _username, string memory _password) public pure returns (bool) {
        if (keccak256(abi.encodePacked(_username)) == keccak256(abi.encodePacked("admin")) &&
            keccak256(abi.encodePacked(_password)) == keccak256(abi.encodePacked("admin"))) {
            return true;
        }
        return false;
    }

    function registerOwner(
        string memory _name,
        string memory _addr,
        string memory _phone,
        string memory _email
    ) public {
        owners[msg.sender] = Owner(_name, _addr, _phone, _email);
        emit OwnerRegistered(msg.sender, _name, _addr, _phone, _email);
    }

    function generateLandID() internal view returns (string memory) {
        uint256 random = uint256(keccak256(abi.encodePacked(block.timestamp, msg.sender))) % 1000000;
        return uint2str(random);
    }

    function uint2str(uint256 _i) internal pure returns (string memory) {
        if (_i == 0) {
            return "000000";
        }
        uint256 j = _i;
        uint256 length;
        while (j != 0) {
            length++;
            j /= 10;
        }
        bytes memory bstr = new bytes(6);
        for (uint256 k = 6; k > 0; k--) {
            if (_i != 0) {
                bstr[k - 1] = bytes1(uint8(48 + _i % 10));
                _i /= 10;
            } else {
                bstr[k - 1] = '0';
            }
        }
        return string(bstr);
    }

    function registerLand(
        string memory _location,
        uint256 _size,
        string memory _landType
    ) public {
        require(bytes(owners[msg.sender].addr).length != 0, "Owner not registered");
        string memory landID = generateLandID();
        lands[landID] = Land(landID, _location, _size, _landType, msg.sender);
        emit LandRegistered(landID, msg.sender, _location, _size, _landType);
    }

    function submitTransaction(
        string memory _landID,
        address _newOwner,
        string memory _transactionType
    ) public {
        require(lands[_landID].owner == msg.sender, "Only the owner can initiate a transaction");
        transactions[msg.sender].push(Transaction(_newOwner, _transactionType, block.timestamp));
        lands[_landID].owner = _newOwner;
        emit TransactionSubmitted(msg.sender, _newOwner, _transactionType, block.timestamp);
    }

    function getLandDetails(string memory _landID) public view returns (Land memory, Owner memory) {
        Land memory land = lands[_landID];
        Owner memory owner = owners[land.owner];
        return (land, owner);
    }

    function getOwnershipHistory(address _owner) public view returns (Transaction[] memory) {
        return transactions[_owner];
    }

    function transferLandOwnership(string memory _landID, address _newOwner) public {
        require(lands[_landID].owner == msg.sender, "Only the current owner can transfer ownership");
        require(bytes(owners[_newOwner].addr).length != 0, "New owner must be registered");

        // Record the transaction
        transactions[msg.sender].push(Transaction(_newOwner, "Transfer", block.timestamp));

        // Update the land ownership
        lands[_landID].owner = _newOwner;

        // Emit the transaction event
        emit TransactionSubmitted(msg.sender, _newOwner, "Transfer", block.timestamp);
    }
}

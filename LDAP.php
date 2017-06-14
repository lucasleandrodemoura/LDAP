/**
 * Classe para manibulação do LDAP.
 * @author Lucas Leandro de Moura <lucasleandrodemoura@gmail.com>
 * @copyright (c) 2017, Lucas Leandro de Moura
 * @link www.adevale.com.br
 * @license Open Source
 * @version 1.0
 */
class LDAP{
 
    private  $ldapserver;
    private  $ldapport;
    private  $basedn;
    private  $basepass; 
    private  $conection;
    private  $dc;
    private  $cn;
    
    
    /**
     * Cria um objeto de conexão ao LDAP, com funcionalidades de listar informações e modificar grupos
     * @param type $ldapserver Host do LDAP
     * @param type $ldapport Porta do LDAP
     * @param type $cn CN de usuário
     * @param type $dc Dominio do LDAP
     * @param type $basedn Montagem da String DN
     * @param type $basepass Senha do LDAP
     */
    function __construct($ldapserver = "", $ldapport = "",$cn = "",$dc = "", $basedn = "", $basepass = "") {
        $this->ldapserver = $ldapserver;
        $this->ldapport = $ldapport;
        $this->dc = $dc;
        $this->cn = $cn;
        $this->basepass = $basepass;
        if($basedn){
            $this->basedn = $basedn;
        }else{
            $this->basedn = "cn=".$this->cn.",dc=".$this->dc;
        }
        
        $this->conection = $this->connect();
        
        $this->bind();
    }

    /**
     * Realiza a conexão na base LDAP
     * @return type Retorna a conexão
     */
    private function connect(){
 
        $connection = ldap_connect($this->ldapserver,$this->ldapport);
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        $this->conection = $connection;
        return $connection;
 
    }
    
    /**
     * Se posiciona no diretório
     * @return boolean Retorna TRUE caso conseguiu se conectar
     */
    private function bind(){
 
        $bind = ldap_bind($this->conection, $this->basedn, $this->basepass);
        if ($bind) { 
            return true;
 
        } else { 
            return false;
        }
    }
    
    /**
     * Busca valores no LDAP
     * @param type $searchdn passa a ARVORE que será analisada
     * @param type $filter Aplica o filtro que o LDAP deverá considerar
     * @param type $attributes array contendo quais tags que deverá ser procuradas
     * @return Retorna um array com todas as informações, se retorna FALSE quer dizer que houve um erro na conexão LDAP
     */
    function search($searchdn, $filter, $attributes = array()){
        
        $busca = ldap_search($this->conection, $searchdn, $filter, $attributes);
 
        if ($busca) {
            ldap_count_entries($this->conection, $busca);
            $dados = ldap_get_entries($this->conection, $busca);
            return $dados;    
        } else {
            return false;
        }
        
    }
    
    /**
     * Modifica os membros do grupo
     * @param type $group Nome do grupo que irá receber os usuários
     * @param type $info Recebe array cotendo todos os mebros <br> EX: <br> $info["member"][0] = "uid={username},ou={GrupoUsuario},dc={Dominio}";
     * @return type Retorna True ou False
     */
    function modifyGroup($group, $info) {
        $dn = "cn=$group,ou=Grupos,dc=".$this->dc;
        return ldap_modify($this->conection, $dn, $info);
    }
    
    /**
     * Retorna o último erro gerado no LDAP
     * @return type Retorna uma String com o erro
     */
    function error(){
        return ldap_error($this->conection);
    }
 
    
    /**
     * Fechar a conexão LDAP
     */
    function close(){
        ldap_close($this->conection);
    }
 
}

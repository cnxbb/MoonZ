<?php
// 分页形式数据提取类

class CPageData{
    public $Debug = false;
    private $m_db;               //外部传入的数据库连接
    public $TableName;           //表名
    public $Where;               //SQL条件语句
    public $PageDisplayNum;      //每页显示的记录数
    public $Fields;              //字段列表
    public $Order;               //SQL排序语句
    public $returnSql;
    public function __construct( $db ) {
        $this->m_db = $db;
        $this->PageDisplayNum = 20;     //默认每页显示20条数据
        $this->returnSql = false;
    }
    //------------------------------------------------------------------------------------
    public function getData( $pindex ) { //获取分页数据
        $echosql = 0;  //调试用 是否打印SQL语句标识
        $ret = array();
        $ret['Debug']['SQL'] = array();
        $ret['Max'] = 0;            //总页数
        $ret['Current'] = $pindex;  //当前页数
        $ret['Data'] = array();     //当前页内的数据
        $ret['Display'] = $this->PageDisplayNum;
        if( !isset( $this->TableName ) || strlen( $this->TableName ) == 0 ) {
            return false;
        }
        if( !isset( $this->PageDisplayNum ) || intval( $this->PageDisplayNum ) < 1 ) {
            return false;
        }
        //------------------------------------------------------------------------------------
        //解析如要提取的字段名
        $FieldStr = "";
        if( isset( $this->Fields ) ) {
            if( is_array( $this->Fields ) ) { //如果有字段数组
                for( $i=0; $i<count( $this->Fields ); $i++ ) {
                    if( $this->Fields[$i] == '*' ) {
                        $FieldStr = '*';
                        break;
                    }
                    if( strlen( $FieldStr ) > 0 ) {
                        $FieldStr .= ', ';
                    }
                    $FieldStr .= $this->Fields[$i];
                }
            } else {
                if( strlen( $this->Fields ) == 0 ) {
                    $FieldStr = '*';
                } else {
                    $FieldStr = $this->Fields;
                }
            }
        } else { //如果没有设置Fields 默认为 * (所有字段)
            $FieldStr = '*';
        }
        //------------------------------------------------------------------------------------
        //取总记录数
        $sql = "select count(*) as Num from {$this->TableName}";
        if( isset( $this->Where ) && strlen( $this->Where ) > 0 ) {
            $sql = $sql . ' ' . $this->Where;
        }
        if( $this->Debug ) {
            echo $sql.":1<BR>";
        }
        array_push( $ret['Debug']['SQL'], $sql );
        $sth = $this->m_db->query( $sql );
        //var_dump( $sth );
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        $RecordCount = $row['Num'];
        if( intval( $RecordCount ) == 0 ) {
            return false;
        }
        //计算总页数
        //echo intval( $RecordCount ), "<BR>";
        //echo intval( $this->PageDisplayNum ), "<BR>";
        $ret['RecordCount'] = $RecordCount;
        $ret['Max'] = intval( intval( $RecordCount ) / intval( $this->PageDisplayNum ) );
        if( fmod( intval( $RecordCount ), intval( $this->PageDisplayNum ) ) > 0 ) {
            $ret['Max']++;
        }
        //取分页内数据
        $sql = "select {$FieldStr} from {$this->TableName}";
        if( isset( $this->Where ) && strlen( $this->Where ) > 0 ) {
            $sql = $sql . ' ' . $this->Where;
        }
        if( $this->Debug ) {
            echo $sql,":2<BR>";
        }
        if( isset( $this->Order ) && strlen( $this->Order ) > 0 ) {
            $sql = $sql . ' ' . $this->Order;
        }
        $sql = $sql .= ' limit ' . strval( ( $pindex - 1 ) * $this->PageDisplayNum ) . ', ' . strval( $this->PageDisplayNum );
        if( $this->Debug ) {
            echo $sql.":3<BR>";
        }
        array_push( $ret['Debug']['SQL'], $sql );
        $sth = $this->m_db->query( $sql );
        while( 1 ) {
            $row = $sth->fetch(PDO::FETCH_ASSOC);
            if( $row == FALSE ) break;
            array_push( $ret['Data'], $row );
        }
        if( !$this->returnSql ) {
            unset( $ret['Debug'] );
        }
        return $ret;
    }

    public function SplitData( $arr, $pindex ) {
        $ret['RecordCount'] = count( $arr );
        $ret['Display'] = $this->PageDisplayNum;
        $ret['Max'] = intval( count( $arr ) / intval( $this->PageDisplayNum ) );
        if( fmod(  count( $arr ) , intval( $this->PageDisplayNum ) ) > 0 ) {
            $ret['Max']++;
        }
        $ret['Current'] = $pindex;
        if( $pindex > $ret['Max'] ) {
            $ret['Current'] = $ret['Max'];
        }
        if( $pindex < 1 ) {
            $ret['Current'] = 1;
        }
        $len = count( $arr ) - ( $pindex - 1 ) * $this->PageDisplayNum;
        if( $len > $this->PageDisplayNum ) {
            $len = $this->PageDisplayNum;
        }
        $ret['Data'] = array_slice( $arr, ( $pindex - 1 ) * $this->PageDisplayNum, $len );
        return $ret;
    }

    public function GetPgeUrl( $arr, $pindex, $display = 10 ) {

    }
    //------------------------------------------------------------------------------------

    public function OutputPageBar( $pd ) {
        $params = array();
        foreach( $_GET as $key => $val ) {
            $params[$key] = $val;
        }
        foreach( $_POST as $key => $val ) {
            $params[$key] = $val;
        }
        $params['page'] = $pd['Current'];
        if( $pd['Current'] <= 10 ) {
            $page_base = 1;
        } else {
            $page_base = intval( $pd['Current'] / 10 ) * 10 + 1;
        }
        $page_end = $pd['Max'] <= $page_base + 9 ? $pd['Max'] : $page_base + 9;
?>
        <script language="JavaScript">
            function xp( page ) {
                $('#xpform #page').val( page );
                $('#xpform').submit();
            }
            function pfsubmit() {
                xp( $('#pagecurrentindex').val() );
                return false;
            }
        </script>
        <form method="GET" action="<?php echo str_replace( '//', '/', $_SERVER['REQUEST_URI']);?>" name="xpform" id="xpform" style="display:none;">
            <?php foreach( $params as $key => $val ) { ?>
            <input type="hidden" name="<?php echo $key;?>" id="<?php echo $key;?>" value="<?php echo $val;?>">
            <?php } ?>
        </form>
        <form onsubmit='javascript:return pfsubmit();' name="pageform" style="margin:0px;padding:0px;" method="GET" action="<?php echo str_replace( '//', '/', $_SERVER['REQUEST_URI']);?>">
            <table id="page" border="0" align="center" cellpadding="0" cellspacing="0" bordercolor="#94bce3" class="page" style="border:1px #94bce3">
                <tr>
                    <td align="center" >
                        <?php if( intval( $pd['Max'] ) < 1 ) { ?>
                        <div>暂无数据</div>
                        <?php } else { ?>
                        <div id="pagetotalpage" style="padding:0 10px 0 2px;">共 <?php echo intval( $pd['Max'] );?> 页</div>
                        <?php } ?>
                    </td>
                    <?php if( intval( $pd['Current'] ) > 1 ) { ?>
                        <td width="60px" align="center">
                            <a href="javascript:xp(<?php echo $pd['Current']-1;?>);">上一页</a>
                        </td>
                    <?php } ?>
                    <?php for( $i=$page_base; $i<=$page_end; $i++ ) { ?>
                        <?php if( $i == $pd['Current'] ) { ?>
                            <td align="center" width="24px" style="background:#016499;padding:2px 0px;">
                                <div style="background-color:#016499;height:100%;">
                                    <font color="#fff"><?php echo $i;?></font>
                                </div>
                            </td>
                        <?php } else { ?>
                            <td align="center" width="24px">
                                <a href="javascript:xp(<?php echo $i;?>);"><?php echo $i;?></a>
                            </td>
                        <?php } ?>
                    <?php } ?>

                    <?php if( $pd['Current'] < $pd['Max'] ) { ?>
                        <td align="center" width="60px">
                            <a href="javascript:xp(<?php echo $pd['Current']+1;?>)">下一页</a>
                        </td>
                    <?php } ?>
                </tr>
            </table>
        </form>
<?php
    }
}


?>
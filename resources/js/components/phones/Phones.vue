<template>
    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <div class="col-12">

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Números de Telefone</h3>

                            <div class="card-tools">

                                <button type="button" class="btn btn-sm btn-primary" @click="newModal">
                                    <i class="fa fa-plus-square"></i>
                                    Cadastrar Novo
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Número</th>
                                    <th>Plataforma</th>
                                    <th>Mensagens</th>
                                    <th>Status</th>
                                    <th>Ação</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="phone in phones.data" :key="phone.id">
                                    <td>{{phone.name}}</td>
                                    <td>{{phone.phone_number }}</td>
                                    <td>{{phone.platform}}</td>
                                    <td>{{phone.total_messages }}</td>
                                    <td>
                                        <a href="#" @click="qrCodeStatus(phone)">Ver Status</a>
                                    </td>
                                    <td>
                                        <a href="#" @click="openQrCodeModal(phone)">
                                            <i class="fa fa-qrcode green"></i>
                                        </a>
                                        /
                                        <router-link :to="`/phones/${phone.id}/messages`">
                                            <i class="fa fa-envelope-open orange"></i>
                                        </router-link>
                                        /
                                        <a href="#" @click="editModal(phone)">
                                            <i class="fa fa-edit blue"></i>
                                        </a>
                                        /
                                        <a href="#" @click="deletePhone(phone.id)">
                                            <i class="fa fa-trash red"></i>
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <pagination :data="phones" @pagination-change-page="getResults"></pagination>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="addNew" tabindex="-1" role="dialog" aria-labelledby="addNew" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" v-show="!editmode">Cadastrar Novo Telefone</h5>
                            <h5 class="modal-title" v-show="editmode">Editar Telefone</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="editmode ? updatePhone() : createPhone()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Descrição</label>
                                    <input v-model="form.name" type="text" name="name"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                    <has-error :form="form" field="name"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Número de Telefone</label>
                                    <input v-model="form.phone_number" type="text" name="phone_number"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('phone_number') }">
                                    <has-error :form="form" field="phone_number"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Plataforma</label>
                                    <select class="form-control" v-model="form.platform">
                                        <option
                                            v-for="platform in platforms" :key="platform.value"
                                            :value="platform.value"
                                            :selected="platform.value == form.platform">{{ platform.name }}</option>
                                    </select>
                                    <has-error :form="form" field="platform"></has-error>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-success">Salvar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog" aria-labelledby="qrCodeModal" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Escanear QrCode</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <h1>Scan QRCode</h1>
                            <p v-if="!qrCodeUrl">Carregando...</p>

                            <img src="" alt="qrcode" id="qrCodeImage">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</template>

<script>
import VueTagsInput from '@johmun/vue-tags-input';

export default {
    components: {
        VueTagsInput,
    },
    data () {
        return {
            editmode: false,
            phones : {},
            form: new Form({
                id : '',
                name: '',
                phone_number : '',
                platform: '',
                platform_api_url:  '',
                total_messages: 0,
                status: false,
            }),
            platforms: [
                { name: 'Outros', value: 'outros' },
                { name: 'Wordpress', value: 'wordpress' }
            ],
            qrCodeUrl: '',
        }
    },
    methods: {
        getResults(page = 1) {
            this.$Progress.start();
            axios.get('api/phone?page=' + page).then(({ data }) => (this.phones = data.data));
            this.$Progress.finish();
        },
        loadPhones(){
            axios.get("api/phone").then(({ data }) => (this.phones = data.data));
        },
        editModal(phone){
            this.editmode = true;
            this.form.reset();
            $('#addNew').modal('show');
            this.form.fill(phone);
        },
        newModal(){
            this.editmode = false;
            this.form.reset();
            $('#addNew').modal('show');
        },
        openQrCodeModal(phone) {
            this.form.reset();
            $('#qrCodeModal').modal('show');
            this.qrCodeGet(phone);
            this.form.fill(phone);
        },
        async qrCodeStatus(phone, alerta = true){
            const phoneNumber = phone.phone_number;
            const tokenData = await axios.post(`${IP_SERVER}${phoneNumber}/${SECRET_KEY}/generate-token`);

            const statusData = await axios.get(`${IP_SERVER}${phoneNumber}/status-session`, {
                headers: {
                    'Authorization': `Bearer ${tokenData.data.token}`
                }
            });

            if(alerta === true)
                return alert(`Esse número está ${statusData.data.status}`);

            if(statusData.data.status == 'CONNECTED')
                return tokenData.data.token;

            $('#qrCodeModal').modal('hide');
            alert('Usuário já está conectado');
        },
        qrCodeGet(phone){
            this.qrCodeUrl = '';
            $('#qrCodeImage').attr('src', null);

            const phoneNumber = phone.phone_number;

        },
        createPhone(){
            this.$Progress.start();
            this.form.post('api/phone')
                .then((data)=>{
                    if(data.data.success){
                        $('#addNew').modal('hide');
                        Toast.fire({
                            icon: 'success',
                            title: data.data.message
                        });
                        this.$Progress.finish();
                        this.loadPhones();

                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: 'Some error occured! Please try again'
                        });
                        this.$Progress.failed();
                    }
                })
                .catch(()=>{
                    Toast.fire({
                        icon: 'error',
                        title: 'Some error occured! Please try again'
                    });
                })
        },
        updatePhone(){
            this.$Progress.start();
            this.form.put('api/phone/'+this.form.id)
                .then((response) => {
                    $('#addNew').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: response.data.message
                    });
                    this.$Progress.finish();
                    this.loadPhones();
                })
                .catch(() => {
                    this.$Progress.fail();
                });
        },
        deletePhone(id){
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.value) {
                    this.form.delete('api/phone/'+id).then(()=>{
                        Swal.fire(
                            'Deleted!',
                            'Your file has been deleted.',
                            'success'
                        );
                        this.loadPhones();
                    }).catch((data)=> {
                        Swal.fire("Failed!", data.message, "warning");
                    });
                }
            })
        },

    },
    mounted() {
    },
    created() {
        this.$Progress.start();
        this.loadPhones();
        this.$Progress.finish();
    },
    filters: {
    },
    computed: {
    },
}
</script>

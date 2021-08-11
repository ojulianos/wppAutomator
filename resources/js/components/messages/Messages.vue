<template>
    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <div class="col-12">

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Mensagens</h3>

                            <div class="card-tools">

                                <button type="button" class="btn btn-sm btn-primary" @click="newModal">
                                    <i class="fa fa-plus-square"></i>
                                    Adicionar Nova
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Mensagem</th>
                                    <th>Tags</th>
                                    <th>Tipo</th>
                                    <th>Ação</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="message in messages.data" :key="message.id">

                                    <td>{{message.description}}</td>
                                    <td>{{message.body | truncate(30, '...')}}</td>
                                    <td>{{message.tags }}</td>
                                    <td>{{message.type }}</td>
                                    <td>
                                        <a href="#" @click="editModal(message)">
                                            <i class="fa fa-edit blue"></i>
                                        </a>
                                        /
                                        <a href="#" @click="deleteProduct(message.id)">
                                            <i class="fa fa-trash red"></i>
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <pagination :data="messages" @pagination-change-page="getResults"></pagination>
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
                            <h5 class="modal-title" v-show="!editmode">Create New Product</h5>
                            <h5 class="modal-title" v-show="editmode">Edit Product</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="editmode ? updateMessage() : createMessage()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Descrição</label>
                                    <input v-model="form.description" type="text" name="description"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('description') }">
                                    <has-error :form="form" field="description"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Mensagem</label>
                                    <textarea v-model="form.body"
                                              type="text"
                                              name="body"
                                              class="form-control"
                                              :class="{ 'is-invalid': form.errors.has('body') }"
                                              rows="4"
                                    ></textarea>
                                    <has-error :form="form" field="body"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Número de Referência</label>
                                    <input v-model="form.reference_id" type="number" name="reference_id"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('reference_id') }">
                                    <has-error :form="form" field="reference_id"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Tipo</label>
                                    <select class="form-control" v-model="form.type">
                                        <option
                                            v-for="tipo in tipos" :key="tipo.code"
                                            :value="tipo.code"
                                            :selected="tipo.code == form.type">{{ tipo.value }}</option>
                                    </select>
                                    <has-error :form="form" field="type"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Tags</label>
                                    <input v-model="form.tags" type="text" name="tags"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('tags') }">
                                    <has-error :form="form" field="tags"></has-error>
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
            messages : {},
            form: new Form({
                id : '',
                description : '',
                reference_id: '',
                type: '',
                body: '',
                tag: '',
                tags:  [],
            }),
            tipos: [
                { code: 'primeiro-contato', value: 'Primeiro Contato', status: true },
                { code: 'mensagem', value: 'Mensagem de Resposta', status: true },
                { code: 'resultados', value: 'Mensagem de Resultados', status: true },
                { code: 'nao-encontrato', value: 'Mensagem de Não Encontrado', status: true },
                { code: 'finalizacao', value: 'Mensagem de Finalização', status: true },
            ],
            autocompleteItems: [],
        }
    },
    methods: {
        getResults(page = 1) {
            this.$Progress.start();
            axios.get(`/api/phoneMessage/${this.$route.params.id}?page=${page}`).then(({ data }) => (this.messages = data.data));
            this.$Progress.finish();
        },
        loadMessages(){
            axios.get(`/api/phoneMessage/${this.$route.params.id}`).then(({ data }) => (this.messages = data.data));
        },
        editModal(message){
            this.editmode = true;
            this.form.reset();
            $('#addNew').modal('show');
            this.form.fill(message);
        },
        newModal(){
            this.editmode = false;
            this.form.reset();
            $('#addNew').modal('show');
        },
        createMessage(){
            this.$Progress.start();

            this.form.post(`/api/phoneMessage/${this.$route.params.id}`)
                .then((data)=>{
                    if(data.data.success){
                        $('#addNew').modal('hide');

                        Toast.fire({
                            icon: 'success',
                            title: data.data.message
                        });
                        this.$Progress.finish();
                        this.loadMessages();

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
        updateMessage(){
            this.$Progress.start();
            this.form.put(`/api/phoneMessage/${this.form.id}`)
                .then((response) => {
                    $('#addNew').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: response.data.message
                    });
                    this.$Progress.finish();

                    this.loadMessages();
                })
                .catch(() => {
                    this.$Progress.fail();
                });

        },
        deleteProduct(id){
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.value) {
                    this.form.delete(`/api/phoneMessage/${id}`).then(()=>{
                        Swal.fire(
                            'Deleted!',
                            'Your file has been deleted.',
                            'success'
                        );
                        this.loadMessages();
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
        this.loadMessages();
        this.$Progress.finish();
    },
    filters: {
        truncate: function (text, length, suffix) {
            return text.substring(0, length) + suffix;
        },
    },
    computed: {
        filteredItems() {
            return this.autocompleteItems.filter(i => {
                return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
            });
        },
    },
}
</script>

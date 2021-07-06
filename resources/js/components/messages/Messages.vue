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

                                    <td>{{message.id}}</td>
                                    <td>{{message.name}}</td>
                                    <td>{{message.description | truncate(30, '...')}}</td>
                                    <td>{{message.category.name}}</td>
                                    <td>{{message.price}}</td>
                                    <!-- <td><img v-bind:src="'/' + message.photo" width="100" alt="message"></td> -->
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

                        <form @submit.prevent="editmode ? updateProduct() : createProduct()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input v-model="form.name" type="text" name="name"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                    <has-error :form="form" field="name"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <input v-model="form.description" type="text" name="description"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('description') }">
                                    <has-error :form="form" field="description"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Price</label>
                                    <input v-model="form.price" type="text" name="price"
                                           class="form-control" :class="{ 'is-invalid': form.errors.has('price') }">
                                    <has-error :form="form" field="price"></has-error>
                                </div>
                                <div class="form-group">

                                    <label>Category</label>
                                    <select class="form-control" v-model="form.category_id">
                                        <option
                                            v-for="(cat,index) in categories" :key="index"
                                            :value="index"
                                            :selected="index == form.category_id">{{ cat }}</option>
                                    </select>
                                    <has-error :form="form" field="category_id"></has-error>
                                </div>
                                <div class="form-group">
                                    <label>Tags</label>
                                    <vue-tags-input
                                        v-model="form.tag"
                                        :tags="form.tags"
                                        :autocomplete-items="filteredItems"
                                        @tags-changed="newTags => form.tags = newTags"
                                    />
                                    <has-error :form="form" field="tags"></has-error>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button v-show="editmode" type="submit" class="btn btn-success">Update</button>
                                <button v-show="!editmode" type="submit" class="btn btn-primary">Create</button>
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
                type: '',
                body: '',
                tags:  [],
            }),
        }
    },
    methods: {
        getResults(page = 1) {
            this.$Progress.start();
            axios.get(`api/phoneMessage/${this.$route.params.id}?page=${page}`).then(({ data }) => (this.messages = data.data));
            this.$Progress.finish();
        },
        loadMessages(){
            axios.get(`api/phoneMessage/${this.$route.params.id}`).then(({ data }) => (this.messages = data.data));
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
        createProduct(){
            this.$Progress.start();

            this.form.post(`api/phoneMessage/${this.$route.params.id}`)
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
        updateProduct(){
            this.$Progress.start();
            this.form.put(`api/phoneMessage/${this.form.id}`)
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
                    this.form.delete(`api/phoneMessage/${id}`).then(()=>{
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
    },
    computed: {
    },
}
</script>
